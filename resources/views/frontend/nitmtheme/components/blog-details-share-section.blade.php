<div class="d-flex align-content-center justify-content-between">
@if(!Settings('hide_social_share_btn') =='1')
        <div class="social_btns">
            <a target="_blank"
               href="https://www.facebook.com/sharer/sharer.php?u={{URL::current()}}"
               class="social_btn fb_bg"> <i class="fab fa-facebook-f"></i>
                {{__('frontend.Facebook')}} </a>
            <a target="_blank"
               href="https://twitter.com/intent/tweet?text={{$blog->title}}&amp;url={{URL::current()}}"
               class="social_btn Twitter_bg"> <i
                    class="fab fa-twitter"></i> {{__('frontend.Twitter')}}</a>
            <a target="_blank"
               href="https://pinterest.com/pin/create/link/?url={{URL::current()}}&amp;description={{$blog->title}}"
               class="social_btn Pinterest_bg"> <i
                    class="fab fa-pinterest-p"></i> {{__('frontend.Pinterest')}}</a>
            <a target="_blank"
               href="https://www.linkedin.com/shareArticle?mini=true&amp;url={{URL::current()}}&amp;title={{$blog->title}}&amp;summary={{$blog->title}}"
               class="social_btn Linkedin_bg"> <i
                    class="fab fa-linkedin-in"></i> {{__('frontend.Linkedin')}}</a>
        </div>
    @endif

        <div class="like_btn d-flex align-items-center gap-2 ">
            <span> {{$blog->likers_count}}</span>
            @if(auth()->check())
                <a href="{{route('blogs.toggleLike',$blog->id)}}">
                    <i class="fa fa-heart fa-2x {{$blog->isLikedBy(auth()->user())?'':'text-danger'}}"></i>
                </a>
            @else
                <a href="#">
                    <i class="fa fa-heart fa-2x"></i>
                </a>
            @endif

        </div>
</div>
