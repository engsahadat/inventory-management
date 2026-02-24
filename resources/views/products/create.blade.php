@extends('layouts.app')
@section('title', __('Add Product - Inventory Management'))
@section('content')
    <div class="page-header">
        <h2>{{ __('Add New Product') }}</h2>
        <p>{{ __('Create a new product in your inventory') }}</p>
    </div>

    <div class="grid grid-1">
        <div class="card">
            <form id="productForm" action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="sku">{{ __('SKU (Stock Keeping Unit)') }} *</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku') }}" placeholder="{{ __('e.g., PRD001') }}">
                    <span class="error-message" id="error-sku"></span>
                </div>

                <div class="form-group">
                    <label for="name">{{ __('Product Name') }} *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('e.g., Laptop') }}">
                    <span class="error-message" id="error-name"></span>
                </div>

                <div class="form-group">
                    <label for="category">{{ __('Category') }} *</label>
                    <input type="text" id="category" name="category" value="{{ old('category') }}"
                        placeholder="{{ __('e.g., Electronics') }}">
                    <span class="error-message" id="error-category"></span>
                </div>

                <div class="form-group">
                    <label for="purchase_price">{{ __('Purchase Price (৳)') }} *</label>
                    <input type="number" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}"
                        step="0.01" placeholder="{{ __('e.g., 100.00') }}">
                    <span class="error-message" id="error-purchase_price"></span>
                </div>

                <div class="form-group">
                    <label for="sell_price">{{ __('Sell Price (৳)') }} *</label>
                    <input type="number" id="sell_price" name="sell_price" value="{{ old('sell_price') }}" step="0.01"
                        placeholder="{{ __('e.g., 200.00') }}">
                    <span class="error-message" id="error-sell_price"></span>
                </div>

                <div class="form-group">
                    <label for="opening_stock">{{ __('Opening Stock Quantity') }} *</label>
                    <input type="number" id="opening_stock" name="opening_stock" value="{{ old('opening_stock', 0) }}"
                        placeholder="{{ __('e.g., 50') }}">
                    <span class="error-message" id="error-opening_stock"></span>
                </div>

                <div class="form-group">
                    <label for="description">{{ __('Description') }}</label>
                    <textarea id="description" name="description" rows="3"
                        placeholder="{{ __('Product description (optional)') }}">{{ old('description') }}</textarea>
                    <span class="error-message" id="error-description"></span>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn">💾 {{ __('Save Product') }}</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            $(document).ready(function () {
                const $form = $('#productForm');
                const $submitBtn = $('#submitBtn');
                const originalBtnText = $submitBtn.html();

                $form.on('submit', function (e) {
                    e.preventDefault();
                    clearErrors();
                    $submitBtn.prop('disabled', true).html('⏳ Saving...');
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        success: function (response) {
                            showSuccessMessage(response.message || 'Product created successfully!');
                            setTimeout(function () {
                                window.location.href = response.redirect || '{{ route("products.index") }}';
                            }, 1000);
                        },
                        error: function (xhr) {
                            $submitBtn.prop('disabled', false).html(originalBtnText);
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                if (errors) {
                                    displayErrors(errors);
                                }
                            } else {
                                const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                                showErrorMessage(message);
                            }
                        }
                    });
                });

                function clearErrors() {
                    $('.error-message').text('');
                    $('.form-group input, .form-group textarea').removeClass('error');
                }

                function displayErrors(errors) {
                    $.each(errors, function (field, messages) {
                        const $errorElement = $('#error-' + field);
                        const $inputElement = $('#' + field);

                        if ($errorElement.length) {
                            $errorElement.text(messages[0]);
                        }
                        if ($inputElement.length) {
                            $inputElement.addClass('error');
                        }
                    });
                }

                function showSuccessMessage(message) {
                    const $alert = $('<div></div>')
                        .addClass('alert alert-success')
                        .text(message)
                        .css({
                            position: 'fixed',
                            top: '20px',
                            right: '20px',
                            zIndex: '9999',
                            minWidth: '300px'
                        });

                    $('body').append($alert);
                    setTimeout(function () { $alert.remove(); }, 3000);
                }

                function showErrorMessage(message) {
                    const $alert = $('<div></div>')
                        .addClass('alert alert-error')
                        .text(message)
                        .css({
                            position: 'fixed',
                            top: '20px',
                            right: '20px',
                            zIndex: '9999',
                            minWidth: '300px'
                        });

                    $('body').append($alert);
                    setTimeout(function () { $alert.remove(); }, 5000);
                }
            });
        </script>
    @endpush
@endsection