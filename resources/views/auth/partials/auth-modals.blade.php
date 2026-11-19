<div class="fixed inset-0 z-[1200] hidden items-center justify-center overflow-y-auto bg-[#231f20bf] p-4"
     role="presentation"
     data-auth-modal-backdrop
     data-auth-modal-open="{{ session('auth_modal') ?? old('_auth_modal', '') }}">
    @include('auth.partials.login-modal')
    @include('auth.partials.register-modal')
    @include('auth.partials.forgot-password-modal')
    @include('auth.partials.reset-password-modal')
    @include('auth.partials.verify-email-modal')
</div>
