<div class="taken-vox-stats {!! empty($related_voxes) || $suggested_voxes->isEmpty() ? 'without-line' : '' !!}">
    <div class="container">
        <h3 class="taken-title">{!! trans('vox.page.vox-daily-limit-reached.stats.title') !!}</h3>
        <p class="vox-stats-subtitle">{!! trans('vox.page.vox-daily-limit-reached.stats.description') !!}</p>
        <a class="video-parent" href="{{ $vox->has_stats ? $vox->getStatsList() : getLangUrl('dental-survey-stats') }}">
            <video id="myVideo" class="video-stats" playsinline autoplay muted loop src="{{ url('new-vox-img/stats.m4v') }}" type="video/mp4" controls=""></video>
        </a>
        <a class="video-parent-mobile" href="{{ $vox->has_stats ? $vox->getStatsList() : getLangUrl('dental-survey-stats') }}">
            <video id="myVideoMobile" class="video-stats" playsinline autoplay muted loop src="{{ url('new-vox-img/stats-mobile.mp4') }}" type="video/mp4" controls=""></video>
        </a>
    </div>

    <div class="tac">
        <a href="{{ $vox->has_stats ? $vox->getStatsList() : getLangUrl('dental-survey-stats') }}" class="blue-button more-surveys">{!! trans('vox.common.check-statictics') !!}</a>
    </div>
</div>