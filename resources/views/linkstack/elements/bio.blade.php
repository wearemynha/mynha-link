        <!-- Short Bio -->
        <style>.description-parent * {margin-bottom: 1em;}.description-parent {padding-bottom: 30px;}</style>
        <center><div class="fadein description-parent dynamic-contrast"><p class="fadein">@if(config('linkstack.allow_user_html') === true){!! $info->littlelink_description !!}@else{{ $info->littlelink_description }}@endif</p></div></center>