@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="h-full flex-auto flex flex-col">
    <div class="px-4 flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold">{{ $title ?? __('Create Custom Field') }}</h3>
        <a href="{{ ns()->url('/dashboard/customprofiletabs/fields') }}" class="rounded-full border ns-inset-button px-3 py-1">
            {{ __('Return') }}
        </a>
    </div>

    <div class="px-4 flex-auto overflow-y-auto">
        <ns-crud-form
            submit-url="{{ ns()->url('/api/crud/customprofiletabs.fields') }}"
            return-url="{{ ns()->url('/dashboard/customprofiletabs/fields') }}"
            src="{{ ns()->url('/api/crud/customprofiletabs.fields/form-config') }}">
        </ns-crud-form>
    </div>
</div>
@endsection
