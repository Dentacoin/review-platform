<div>
	<a class="respondents-shown" href="{{ url('cms/vox/explorer/'.$item->id) }}">
		<!-- {{ $item->realRespondentsCountForAdminPurposes() }} -->
	</a>

	<a href="javascript:;" class="show-respondents" vox-id="{{ $item->id }}">show</a>
</div>