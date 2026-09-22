<aside class="daily-health-tip" aria-label="Health tip">
    <strong>Health tip</strong>
    <div class="health-tip-window"><p class="health-tip-text">@foreach (collect(\App\Services\DailyHealthTipService::TIPS)->shuffle() as $tip)<span>{{ $tip }}</span>@if (!$loop->last)<span class="health-tip-separator" aria-hidden="true">&bull;</span>@endif @endforeach</p></div>
</aside>
