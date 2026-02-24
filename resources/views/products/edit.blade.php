@extends('layouts.app')
@section('title', __('Edit Product - Inventory Management'))
@section('content')
    <div class="page-header">
        <h2>{{ __('Edit Product') }}</h2>
        <p>{{ __('Update product information') }}</p>
    </div>
    <div class="grid grid-2">
        <div class="card">
            <form id="productForm" action="{{ route('products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="sku">{{ __('SKU (Stock Keeping Unit)') }} *</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                    <span class="error-message" id="error-sku"></span>
                </div>
                <div class="form-group">
                    <label for="name">{{ __('Product Name') }} *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}">
                    <span class="error-message" id="error-name"></span>
                </div>

                <div class="form-group">
                    <label for="category">{{ __('Category') }} *</label>
                    <input type="text" id="category" name="category" value="{{ old('category', $product->category) }}">
                    <span class="error-message" id="error-category"></span>
                </div>

                <div class="form-group">
                    <label for="purchase_price">{{ __('Purchase Price (৳)') }} *</label>
                    <input type="number" id="purchase_price" name="purchase_price"
                        value="{{ old('purchase_price', $product->purchase_price) }}" step="0.01">
                    <span class="error-message" id="error-purchase_price"></span>
                </div>

                <div class="form-group">
                    <label for="sell_price">{{ __('Sell Price (৳)') }} *</label>
                    <input type="number" id="sell_price" name="sell_price"
                        value="{{ old('sell_price', $product->sell_price) }}" step="0.01">
                    <span class="error-message" id="error-sell_price"></span>
                </div>

                <div class="form-group">
                    <label for="description">{{ __('Description') }}</label>
                    <textarea id="description" name="description"
                        rows="3">{{ old('description', $product->description) }}</textarea>
                    <span class="error-message" id="error-description"></span>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn">💾 {{ __('Update Product') }}</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem;">{{ __('Current Stock Information') }}</h3>
            <div class="stat-card" style="margin-bottom: 1rem;">
                <div class="stat-label">{{ __('Current Stock Quantity') }}</div>
                <div class="stat-value" style="font-size: 2.5rem;">{{ $product->current_stock }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">{{ __('Current Stock Value (at Purchase Price)') }}</div>
                <div class="stat-value" style="font-size: 1.5rem;">
                    ৳{{ number_format($product->current_stock * $product->purchase_price, 2) }}</div>
            </div>
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
                    $submitBtn.prop('disabled', true).html('⏳ Updating...');
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        success: function (response) {
                            showSuccessMessage(response.message || 'Product updated successfully!');
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