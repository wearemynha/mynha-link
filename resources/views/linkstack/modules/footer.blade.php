<div class="container">
	<div class="footer fadein" style="margin:5% 0px 35px 0px;">
	@if(config('linkstack.display_footer') === true)
		@if(config('linkstack.display_footer_home') === true)<a class="footer-hover spacing" href="{{ config('linkstack.home_footer_link') ?: url('') }}">{{footer('Home')}}</a>@endif
		@if(config('linkstack.display_footer_terms') === true)<a class="footer-hover spacing" href="{{ url('') }}/pages/{{ strtolower(footer('Terms')) }}">{{footer('Terms')}}</a>@endif
		@if(config('linkstack.display_footer_privacy') === true)<a class="footer-hover spacing" href="{{ url('') }}/pages/{{ strtolower(footer('Privacy')) }}">{{footer('Privacy')}}</a>@endif
		@if(config('linkstack.display_footer_contact') === true)<a class="footer-hover spacing" href="{{ url('') }}/pages/{{ strtolower(footer('Contact')) }}">{{footer('Contact')}}</a>@endif
	@endif
	</div>

	@if(config('linkstack.display_credit') === true)
	{{-- Removed class spacing --}}
	<a style="text-decoration: none;" class="" href="https://linkstack.org" target="_blank" title="{{__('messages.Learn more about LinkStack')}}">
		<div style="vertical-align: middle;display: inline-block;padding-bottom:50px;" class="credit-hover hvr-grow fadein">
			<img style="width:200px" class="" src="{{ asset('assets/linkstack/images/powered-by-linkstack.svg') }}" alt="LinkStack">
		</div>
	</a>
	@endif
	</div>
