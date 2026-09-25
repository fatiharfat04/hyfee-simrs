@props(['headers' => []])

<div {{ $attributes->class(['overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm']) }}>
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
            <tr>
                @foreach ((array) $headers as $header)
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-600">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
