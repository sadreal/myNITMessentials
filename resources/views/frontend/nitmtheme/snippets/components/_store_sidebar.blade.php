<div class="course-filter-sidebar show">
    <button type="button" class="close text-reset d-lg-none" aria-label="Close" data-bs-dismiss="filter">
        <span aria-hidden="true">×</span>
    </button>
    <div class="course-filter-limit">
        <div class="course-filter-item w-100">
            <h5>
                {{__('frontend.Category')}}
            </h5>

            <div data-type="component-nonExisting"
                 data-preview=""
                 data-table=""
                 data-select="id,title"
                 data-order=""
                 data-limit=""
                 data-where-status="1"
                 data-view="_store_single_sidebar_category"
                 data-model="Modules\Store\Entities\ProductCategory"
                 data-with=""
            >
                <div class="dynamicData"
                     data-dynamic-href="{{routeIsExist('getDynamicData')?route('getDynamicData'):''}}"></div>
            </div>

        </div>
        <div class="course-filter-item w-100">
            <h5>
                {{__('product.Sort By')}}
            </h5>
            <div data-type="component-nonExisting"
                 data-preview=""
                 data-table=""
                 data-select="id,title"
                 data-order=""
                 data-limit=""
                 data-view="_single_sidebar_sort_by"
                 data-model=""
                 data-with=""
            >
                <div class="dynamicData"
                     data-dynamic-href="{{routeIsExist('getDynamicData')?route('getDynamicData'):''}}"></div>
            </div>


        </div>
        {{--        <div class="course-filter-item w-100">--}}
        {{--            <h5>--}}
        {{--                {{__('frontend.Instructor')}}--}}
        {{--            </h5>--}}

        {{--            <div data-type="component-nonExisting"--}}
        {{--                 data-preview=""--}}
        {{--                 data-table=""--}}
        {{--                 data-select=""--}}
        {{--                 data-order=""--}}
        {{--                 data-limit=""--}}
        {{--                 data-where-status="1"--}}
        {{--                 data-where-role_id="2"--}}
        {{--                 data-view="_store_single_sidebar_instructor"--}}
        {{--                 data-model="App\Models\User"--}}
        {{--                 data-with=""--}}
        {{--            >--}}
        {{--                <div class="dynamicData"--}}
        {{--                     data-dynamic-href="{{routeIsExist('getDynamicData')?route('getDynamicData'):''}}"></div>--}}
        {{--            </div>--}}

        {{--        </div>--}}

    </div>

</div>
