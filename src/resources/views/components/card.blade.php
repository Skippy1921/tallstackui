@php
    $personalize = $classes();
@endphp

<div @class($personalize['wrapper.first'])>
    <div @class($personalize['wrapper.second'])>
        @if ($header)
            <div @class([$personalize['header.wrapper'], $colors['background']])>
                <div @class($personalize['header.text.size'])>
                    {{ $header }}
                </div>
            </div>
        @endif
        <div {{ $attributes->class($personalize['body']) }}>
            {{ $slot }}
        </div>
        @if ($footer)
            <div @class($personalize['footer.wrapper'])>
                <div @class($personalize['footer.text'])>
                    {{ $footer }}
                </div>
            </div>
        @endif
    </div>
</div>
