@props(['text'])

@php
    // Убираем \n и \" из текста
    $normalized = str_replace(['\n', '\"'], ["\n", '"'], $text);

    // Разбиваем на строки
    $lines = preg_split('/\r\n|\r|\n/', $normalized);

    $specs = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;

        // Заголовок секции — начинается с <strong>
        if (str_starts_with($line, '<strong>')) {
            $clean = strip_tags($line);
            $clean = rtrim($clean, ':');

            $specs[] = [
                'type' => 'header',
                'text' => $clean,
            ];
            continue;
        }

        // Обычная строка: ключ: значение
        if (strpos($line, ':') !== false) {
            [$key, $value] = explode(':', $line, 2);

            $specs[] = [
                'type' => 'item',
                'key' => trim($key),
                'value' => trim($value),
            ];
            continue;
        }

        // Если строка без двоеточия — просто текст
        $specs[] = [
            'type' => 'text',
            'text' => $line,
        ];
    }
@endphp

<div class="w-[1057px]">

    @foreach ($specs as $index => $item)

        {{-- СТРОКА-ЗАГОЛОВОК --}}
        @if ($item['type'] === 'header')
            <div class="bg-[#f2f2f2] rounded-md
                        h-[38px] flex items-center pl-[17px] mb-2
                        font-bold text-[14px] text-[#231F20]
                        {{-- shadow-[0_2px_8px_rgba(0,0,0,0.20)] --}}
            ">
                {{ $item['text'] }}
            </div>
        @endif

        {{-- ОБЫЧНАЯ СТРОКА --}}
        @if ($item['type'] === 'item')
            <div class="flex h-[36px] text-[15px] text-[#231F20] mb-2">

                {{-- Левая колонка --}}
                <div class="w-[431px] pl-[17px] pt-[15px] flex items-center">
                    {{ $item['key'] }}
                </div>

                {{-- Правая колонка --}}
                <div class="w-[626px] pl-[17px] pt-[15px] flex items-center font-bold">
                    {{ $item['value'] }}
                </div>
            </div>

            {{-- Пунктирная линия — только если следующая строка НЕ заголовок --}}
            @php
                $next = $specs[$index + 1] ?? null;
            @endphp

            @if ($next && $next['type'] !== 'header')
                <div class="border-b border-dashed border-gray-300"></div>
            @endif
        @endif

        {{-- Обычный текст без двоеточия --}}
        @if ($item['type'] === 'text')
            <div class="h-[36px] flex items-center pl-[17px] text-[15px] text-[#231F20]">
                {{ $item['text'] }}
            </div>
        @endif

    @endforeach

</div>
