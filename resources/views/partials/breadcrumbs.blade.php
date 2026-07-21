<nav class="text-sm mb-6">
    <ol class="flex items-center gap-2 text-gray-600">

        @foreach($items as $item)
            @if(isset($item['url']))
                <li>
                    <a href="{{ $item['url'] }}" class="hover:text-red-600">
                        {{ $item['title'] }}
                    </a>
                </li>
                <li>&bull;</li>
            @else
                <li class="text-gray-900 font-medium">
                    {{ $item['title'] }}
                </li>
            @endif
        @endforeach

    </ol>
</nav>
