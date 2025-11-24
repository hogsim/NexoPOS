@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="h-full flex-auto flex flex-col">
    <div class="px-4 flex justify-between items-center">
        <h3 class="text-xl font-semibold">{{ $title ?? __('Custom Profile Fields') }}</h3>
    </div>

    <div class="px-4 flex-auto overflow-hidden">
        <ns-crud
            src="{{ ns()->url('/api/crud/customprofiletabs.fields') }}"
            create-url="{{ ns()->url('/dashboard/customprofiletabs/fields/create') }}"
            mode="table">
        </ns-crud>
    </div>
</div>
@endsection
