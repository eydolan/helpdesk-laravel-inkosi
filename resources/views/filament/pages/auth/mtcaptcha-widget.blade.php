<div class="fi-fo-field-wrp">
    <div id="mtcaptcha-container"></div>
    <input type="hidden" name="mtcaptcha-verifiedtoken" id="mtcaptcha-verifiedtoken" />
    
    @error('mtcaptcha')
        <p class="fi-fo-field-error-message text-sm text-danger-600 dark:text-danger-400">
            {{ $message }}
        </p>
    @enderror
</div>

@push('scripts')
<script src="https://service.mtcaptcha.com/mtcv1/clientapi/mtcaptcha.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof mtcaptcha !== 'undefined') {
            mtcaptcha.render('mtcaptcha-container', {
                sitekey: '{{ $siteKey }}',
                theme: 'light',
                callback: function(token) {
                    document.getElementById('mtcaptcha-verifiedtoken').value = token;
                },
                'error-callback': function() {
                    document.getElementById('mtcaptcha-verifiedtoken').value = '';
                }
            });
        }
    });
</script>
@endpush
