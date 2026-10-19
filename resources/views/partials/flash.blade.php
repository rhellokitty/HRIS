{{-- Flash message & validation summary (borderless semantic banners) --}}
@if (session('success'))
    <div class="alert-success animate-fade-up" role="status">
        <x-icon name="check-circle" class="mt-0.5 h-4 w-4" />
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert-danger animate-fade-up" role="alert">
        <x-icon name="x-circle" class="mt-0.5 h-4 w-4" />
        <span>{{ session('error') }}</span>
    </div>
@endif

@if (session('warning'))
    <div class="alert-warning animate-fade-up" role="status">
        <x-icon name="exclamation" class="mt-0.5 h-4 w-4" />
        <span>{{ session('warning') }}</span>
    </div>
@endif

@if (session('info'))
    <div class="alert-info animate-fade-up" role="status">
        <x-icon name="info" class="mt-0.5 h-4 w-4" />
        <span>{{ session('info') }}</span>
    </div>
@endif

@if ($errors->any() && ! ($hideErrorSummary ?? false))
    <div class="alert-danger animate-fade-up" role="alert">
        <x-icon name="exclamation" class="mt-0.5 h-4 w-4" />
        <ul class="flex flex-col gap-0.5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
