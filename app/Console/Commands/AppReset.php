<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Traits\UploadTheme;
use App\User;
use Database\Seeders\EdumeThemeSeeder;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Appearance\Entities\Theme;
use Modules\Appearance\Entities\ThemeCustomize;
use Modules\CourseSetting\Entities\Course;
use Modules\FrontendManage\Entities\FrontPage;
use Modules\FrontendManage\Entities\HeaderMenu;
use Modules\ModuleManager\Entities\InfixModuleManager;
use Modules\ModuleManager\Entities\Module;
use Modules\ModuleManager\Http\Controllers\ModuleManagerController;
use Modules\RolePermission\Entities\Permission;
use Modules\RolePermission\Entities\RolePermission;

class AppReset extends Command
{
    use UploadTheme;

    protected $signature = 'app:reset';


    protected $description = 'Reset Database';


    public function handle()
    {

        if (!config('app.demo_mode')) {
            return false;
        }
        try {

            $this->startResetting();

            $this->migrateFresh();

            $this->dbSeed();

            $this->passportInstall();

            $this->generateNewKey();

            $this->removeUploadedImageFile();

            $this->resetCustomCssJsFiles();

            $this->changeHomepage();

            $this->activeLanguages();

            $this->modules();

            $this->headerMenu();

            $this->activeThemes();

            $this->assignAllFrontendPermisionToStudent();

            $this->utitity();

            $this->endResetting();


        } catch (Exception $exception) {
            dd($exception);
        }
        return true;
    }

    public function startResetting()
    {
        Storage::put('.app_resetting', '');
        Storage::put('.reset_log', now()->toDateTimeString());
    }

    protected function migrateFresh()
    {
        try {
            $modules = Module::all();
            foreach ($modules as $module) {
                $moduleCheck = \Nwidart\Modules\Facades\Module::find($module->name);

                if ($moduleCheck) {
                    $moduleCheck->disable();
                }
            }

        } catch (Exception $exception) {

        }
        Artisan::call('db:wipe', array('--force' => true));
        Artisan::call('migrate', array('--force' => true));

    }

    protected function dbSeed()
    {
        Artisan::call('db:seed', array('--force' => true));
        User::where('id',1)->update(['dark_mode' => 1]);
        Course::take(5)->update([
            'feature' => 1
        ]);
    }

    protected function passportInstall()
    {
        Artisan::call('migrate', [
            '--path' => 'vendor/laravel/passport/database/migrations',
            '--force' => true,

        ]);
        Artisan::call('passport:install');
    }

    protected function generateNewKey()
    {
        Artisan::call('key:generate', ['--force' => true]);
    }

    public function removeUploadedImageFile()
    {
        $path = 'public/uploads/main/images';
        $this->delete_directory($path);

        $path = 'public/uploads/main/file';
        $this->delete_directory($path);

        $path = 'public/database-backup';
        $this->delete_directory($path);
    }

    public function resetCustomCssJsFiles()
    {
        $css_path = 'public/frontend/nitmtheme/css/custom.css';
        $js_path = 'public/frontend/nitmtheme/js/custom.js';
        File::put($css_path, "");
        File::put($js_path, "");
    }

    public function changeHomepage()
    {
        $new = FrontPage::where('slug', 'home1')->first();
        if ($new) {
            $new->homepage = 1;
            $new->save();
            FrontPage::where('slug', '/')->update([
                'homepage' => 0
            ]);
        }
    }

    public function activeLanguages()
    {
        Cache::forget('LanguageList_main');
        Language::whereIn('code', ['en', 'bn', 'ar', 'fr', 'de', 'it', 'pt', 'ru', 'es', 'tr', 'vi'])->update(['status' => 1]);
    }

    public function modules()
    {
        if (!config('app.has_demo_module')) {
            return false;
        }

        $moduleManager = new ModuleManagerController();

        $modules = $moduleManager->availableModules();
        $ignore = ['SaasBranch', 'LmsSaas', 'LmsSaasMD'];
        foreach ($modules as $module) {
            $provider_file = base_path() . '/' . 'Modules/' . $module->name . '/Providers/' . $module->name . 'ServiceProvider.php';
            if (!file_exists($provider_file) || in_array($module->name, $ignore)) {
                continue;
            }

            $this->moduleInstallActive($module->name);

        }
        AddLmsId();

    }

    public function moduleInstallActive($name)
    {
        try {
            InfixModuleManager::updateOrCreate([
                'name' => $name,
            ], [
                'name' => $name,
                'email' => User::first()->email,
                'purchase_code' => time(),
                'installed_domain' => url('/'),
                'activated_date' => now(),
                'checksum' => md5(time())
            ]);

            $dataPath = 'Modules/' . $name . '/' . $name . '.json';
            $strJsonFileContents = file_get_contents($dataPath);
            $array = json_decode($strJsonFileContents, true);
            $migrations = (array)$array[$name]['migration'] ?? [];

            $ModuleManage = Module::where('name', $name)->first();
            $ModuleManage->status = 1;
            $ModuleManage->save();

            $moduleCheck = \Nwidart\Modules\Facades\Module::find($name);
            if ($moduleCheck) {
                $moduleCheck->enable();
            }
            foreach ($migrations as $value) {
                $path = 'Modules/' . $name . '/Database/Migrations/' . $value;
                if (file_exists($path)) {
                    Artisan::call('migrate',
                        array(
                            '--path' => $path,
                            '--force' => true));

                }
            }


            $seeder_file = base_path() . '/' . 'Modules/' . $name . '/Database/Seeders/' . $name . 'DatabaseSeeder.php';


            if (file_exists($seeder_file)) {
                $class = "\Modules\\" . $name . "\Database\Seeders\\" . $name . "DatabaseSeeder";
                $seed = new $class();
                $seed->run();
            } else {
                Log::error($name . ' Not Seeded');
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function headerMenu()
    {
        Model::unguard();
        HeaderMenu::truncate();
        $pages = FrontPage::all();

        $defaultHome = $pages->where('slug', '/')->first();
        $course = $pages->where('slug', '/courses')->first();
        $quiz = $pages->where('slug', '/quizzes')->first();
        $class = $pages->where('slug', '/classes')->first();


        $menus = [
            [
                'id' => 1,
                'type' => $defaultHome->is_static ? 'Static Page' : 'Dynamic Page',
                'title' => $defaultHome->title,
                'element_id' => $defaultHome->id,
                'link' => url($defaultHome->slug),
                'parent_id' => null,
                'position' => 1,
                'show' => 0,
                'is_newtab' => 0,
                'mega_menu' => 0,
                'mega_menu_column' => 2,
            ],
            [
                'id' => 2,
                'type' => $course->is_static ? 'Static Page' : 'Dynamic Page',
                'title' => $course->title,
                'element_id' => $course->id,
                'link' => url($course->slug),
                'parent_id' => null,
                'position' => 2,
                'show' => 0,
                'is_newtab' => 0,
                'mega_menu' => 0,
                'mega_menu_column' => 2,
            ], [
                'id' => 3,
                'type' => $quiz->is_static ? 'Static Page' : 'Dynamic Page',
                'title' => $quiz->title,
                'element_id' => $quiz->id,
                'link' => url($quiz->slug),
                'parent_id' => null,
                'position' => 3,
                'show' => 0,
                'is_newtab' => 0,
                'mega_menu' => 0,
                'mega_menu_column' => 2,
            ], [
                'id' => 4,
                'type' => $class->is_static ? 'Static Page' : 'Dynamic Page',
                'title' => $class->title,
                'element_id' => $class->id,
                'link' => url($class->slug),
                'parent_id' => null,
                'position' => 4,
                'show' => 0,
                'is_newtab' => 0,
                'mega_menu' => 0,
                'mega_menu_column' => 2,
            ], [
                'id' => 5,
                'type' => 'Custom Link',
                'title' => 'Others',
                'element_id' => null,
                'link' => '#',
                'parent_id' => null,
                'position' => 5,
                'show' => 0,
                'is_newtab' => 0,
                'mega_menu' => 0,
                'mega_menu_column' => 2,
            ]
        ];
        HeaderMenu::insert($menus);

        if (config('app.has_demo_module')) {
            $menu = [
                'id' => 6,
                'type' => 'Custom Link',
                'title' => 'Addons',
                'element_id' => null,
                'link' => '#',
                'parent_id' => null,
                'position' => 6,
                'show' => 0,
                'is_newtab' => 0,
                'mega_menu' => 0,
                'mega_menu_column' => 2,
            ];
            HeaderMenu::create($menu);

        }
        foreach ($pages as $key => $page) {
            if (in_array($page->slug, ['home0','home1', 'home2', 'home3', 'home4', 'home5', 'home6'])) {
                $item = [
                    'type' => $page->is_static ? 'Static Page' : 'Dynamic Page',
                    'title' => $page->title,
                    'element_id' => $page->id,
                    'link' => url($page->slug),
                    'parent_id' => 1,
                    'position' => ++$key,
                    'show' => 0,
                    'is_newtab' => 0,
                    'mega_menu' => 0,
                    'mega_menu_column' => 2,
                ];
                HeaderMenu::create($item);
            } elseif (in_array($page->slug, [
                '/instructors',
                '/contact-us',
                '/about-us',
                '/become-instructor',
                '/blog',
                'certificate-verification'
            ])) {
                $item = [
                    'type' => $page->is_static ? 'Static Page' : 'Dynamic Page',
                    'title' => $page->title,
                    'element_id' => $page->id,
                    'link' => url($page->slug),
                    'parent_id' => 5,
                    'position' => ++$key,
                    'show' => 0,
                    'is_newtab' => 0,
                    'mega_menu' => 0,
                    'mega_menu_column' => 2,
                ];
                HeaderMenu::create($item);
            } elseif (in_array($page->slug, [
                    '/course/subscription',
                    '/bundle-subscription/courses',
                    '//upcoming-courses',
                    '/membership-registration',
                    '/membership',
                    '/affiliate',
                    '/appointment/tutor-finder',
                    '/appointment',
                    '/calendar-view',
                    '/offer',
                    '/forum',
                    '/store',
                ]) && config('app.has_demo_module')) {
                $item = [
                    'type' => $page->is_static ? 'Static Page' : 'Dynamic Page',
                    'title' => $page->title,
                    'element_id' => $page->id,
                    'link' => url($page->slug),
                    'parent_id' => 6,
                    'position' => ++$key,
                    'show' => 0,
                    'is_newtab' => 0,
                    'mega_menu' => 0,
                    'mega_menu_column' => 2,
                ];
                HeaderMenu::create($item);
            }
        }


    }

    public function activeThemes()
    {
        $demo_theme = config('app.demo_theme');
        if (empty($demo_theme)) {
            return false;
        }

        $str = file_get_contents(storage_path('app/theme_' . $demo_theme . '.json'));
        $json = json_decode($str, true);
        if (!$json) {
            return false;
        }

        $theme = Theme::updateOrCreate([
            'name' => $json['name'],
        ], [
            'user_id' => 1,
            'name' => $json['name'],
            'title' => $json['title'],
            'image' => $json['image'],
            'item_code' => null,
            'description' => $json['description'],
            'version' => $json['version'],
            'folder_path' => $json['folder_path'],
            'live_link' => $json['live_link'],
            'tags' => $json['tags'],
            'is_active' => $json['is_active'] ?? 0,
            'status' => $json['status'] ?? 0,
        ]);


        ThemeCustomize::updateOrCreate([
            'theme_name' => $json['name']
        ], [
            'name' => $theme->name . ' Default',
            'theme_id' => $theme->id,
            'is_default' => 1,
            'created_by' => 1,
            'primary_color' => $json['color']['primary_color'] ?? '',
            'secondary_color' => $json['color']['secondary_color'] ?? '',
            'footer_background_color' => $json['color']['footer_background_color'] ?? '',
            'footer_headline_color' => $json['color']['footer_headline_color'] ?? '',
            'footer_text_color' => $json['color']['footer_text_color'] ?? '',
            'footer_text_hover_color' => $json['color']['footer_text_hover_color'] ?? '',
        ])->first();

//active theme
        Theme::query()->update([
                'is_active' => 0
            ]
        );
        Theme::where('id', $theme->id)->update([
            'is_active' => 1
        ]);

        UpdateGeneralSetting('frontend_active_theme', $theme->name);


        //change homepage
        $new = FrontPage::where('slug', '/')->first();
        if ($new) {
            FrontPage::query()->update([
                'homepage' => 0
            ]);
            $new->homepage = 1;
            $new->save();

        }

        if ($demo_theme == 'edume') {
            $seed = new EdumeThemeSeeder();
            $seed->run();
        }


    }

    public function assignAllFrontendPermisionToStudent()
    {
        $permissions=Permission::where('backend',0)->get();
        foreach ($permissions as $permission){
            RolePermission::updateOrCreate([
                'role_id'=>3,
                'permission_id'=>$permission->id,
            ]);
        }
    }

    public function utitity()
    {

        try {
            Cache::forget('SidebarPermissionList_0main');
            Cache::forget('SidebarPermissionList_1main');
            Artisan::call('optimize:clear');
            File::delete(File::glob('bootstrap/cache/*.php'));
            File::delete(File::glob('storage/framework/laravel-excel/*'));
            File::delete(File::glob('storage/framework/cache/data'));
            if (config('app.env') == 'production') {
                array_map('unlink', array_filter((array)glob(storage_path('logs/*.log'))));
            }
            array_map('unlink', array_filter((array)glob(storage_path('debugbar/*.json'))));


            envu([
                'APP_DEBUG' => env('app_env') == 'local' ? "true" : 'false',
                'FORCE_HTTPS' => "false",
            ]);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }

    }

    public function endResetting()
    {
        Storage::delete('.app_resetting');

    }
}
