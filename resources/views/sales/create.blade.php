@extends('layouts.app')

@section('title', __('Create Sale - Inventory Management'))
@section('content')
    <div class="page-header">
        <h2>{{ __('Create New Sale') }}</h2>
        <p>{{ __('Record a new sales transaction') }}</p>
    </div>

    <form id="saleForm" action="{{ route('sales.store') }}" method="POST">
        @csrf

        <div class="grid grid-2" style="align-items: start;">
            <!-- Left Column: Sale Information -->
            <div>
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem;">{{ __('Customer Information') }}</h3>

                    <div class="form-group">
                        <label for="customer_name">{{ __('Customer Name') }} *</label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}"
                            placeholder="{{ __('e.g., John Doe') }}">
                        <span class="error-message" id="error-customer_name"></span>
                    </div>

                    <div class="form-group">
                        <label for="sale_date">{{ __('Sale Date') }} *</label>
                        <input type="date" id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}">
                        <span class="error-message" id="error-sale_date"></span>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1.5rem;">{{ __('Sale Items') }}</h3>
                    <span class="error-message" id="error-products"></span>

                    <div id="sale-items">
                        <div class="sale-item" data-item="0"
                            style="padding: 1rem; background: #f7fafc; border-radius: 4px; margin-bottom: 1rem;">
                            <div class="grid grid-4" style="gap: 0.75rem; align-items: end;">
                                <div class="form-group" style="margin-bottom: 0; grid-column: span 2;">
                                    <label>{{ __('Product') }} *</label>
                                    <select name="products[0][product_id]" class="product-select" data-index="0">
                                        <option value="">{{ __('Select Product') }}</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->sell_price }}"
                                                data-stock="{{ $product->current_stock }}">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error-message" id="error-products-0-product_id"></span>
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>{{ __('Quantity') }} *</label>
                                    <input type="number" name="products[0][quantity]" min="1" value="1"
                                        class="quantity-input" data-index="0">
                                    <span class="error-message" id="error-products-0-quantity"></span>
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label>{{ __('Unit Price (৳)') }} *</label>
                                    <input type="number" name="products[0][unit_price]" step="0.01" class="price-input"
                                        readonly data-index="0">
                                    <span class="error-message" id="error-products-0-unit_price"></span>
                                </div>
                            </div>

                            <div
                                style="margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                                <div class="stock-info" style="font-size: 0.875rem; color: #718096;"></div>
                                <div class="item-total" style="font-weight: 600; color: #2d3748;">{{ __('Total') }}: ৳0.00</div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary" id="addItemBtn">➡ {{ __('Add Another Item') }}</button>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div>
                <div class="card" style="position: sticky; top: 20px;">
                    <h3 style="margin-bottom: 1.5rem;">{{ __('Sale Summary') }}</h3>

                    <div style="margin-bottom: 1.5rem;">
                        <div
                            style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                            <span>{{ __('Subtotal') }}:</span>
                            <span id="summary-subtotal" style="font-weight: 600;">৳0.00</span>
                        </div>

                        <div class="form-group" style="margin-top: 1rem;">
                            <label for="discount_amount">{{ __('Discount Amount (৳)') }}</label>
                            <input type="number" id="discount_amount" name="discount_amount"
                                value="{{ old('discount_amount', 0) }}" step="0.01" min="0" placeholder="0.00">
                            <span class="error-message" id="error-discount_amount"></span>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                            <span>{{ __('After Discount') }}:</span>
                            <span id="summary-after-discount" style="font-weight: 600;">৳0.00</span>
                        </div>

                        <div class="form-group" style="margin-top: 1rem;">
                            <label for="vat_percentage">{{ __('VAT Percentage (%)') }}</label>
                            <input type="number" id="vat_percentage" name="vat_percentage"
                                value="{{ old('vat_percentage', 0) }}" step="0.01" min="0" max="100" placeholder="0">
                            <span class="error-message" id="error-vat_percentage"></span>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                            <span>{{ __('VAT Amount') }}:</span>
                            <span id="summary-vat" style="font-weight: 600;">৳0.00</span>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; padding: 0.75rem 0; margin-top: 0.5rem; background: #edf2f7; padding: 1rem; border-radius: 4px;">
                            <span style="font-size: 1.125rem; font-weight: 700;">{{ __('Total Amount') }}:</span>
                            <span id="summary-total"
                                style="font-size: 1.25rem; font-weight: 700; color: #2b6cb0;">৳0.00</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="paid_amount">{{ __('Paid Amount (৳)') }} *</label>
                        <input type="number" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', 0) }}"
                            step="0.01" min="0" placeholder="0.00">
                        <span class="error-message" id="error-paid_amount"></span>
                    </div>

                    <div id="change-due"
                        style="display: flex; justify-content: space-between; padding: 0.75rem; background: #f7fafc; border-radius: 4px; margin-top: 1rem;">
                        <span style="font-weight: 600;">{{ __('Change/Due') }}:</span>
                        <span id="summary-change" style="font-weight: 700;">৳0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-2" style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" id="submitBtn">💾 {{ __('Create Sale') }}</button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>


    @push('scripts')
        <script>
            let itemCount = 1;

            $(document).ready(function () {
                const $form = $('#saleForm');
                const $submitBtn = $('#submitBtn');
                const originalBtnText = $submitBtn.html();
                $('#addItemBtn').on('click', function () {
                    addSaleItem();
                });
                $('#discount_amount, #vat_percentage, #paid_amount').on('input', function () {
                    calculateTotals();
                });

                // Form submission
                $form.on('submit', function (e) {
                    e.preventDefault();
                    clearErrors();
                    $submitBtn.prop('disabled', true).html('⏳ Creating Sale...');
                    // Submit form via AJAX
                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: $form.serialize(),
                        success: function (response) {
                            showSuccessMessage(response.message || 'Sale created successfully!');
                            setTimeout(function () {
                                window.location.href = response.redirect || '{{ route("sales.index") }}';
                            }, 1000);
                        },
                        error: function (xhr) {
                            $submitBtn.prop('disabled', false).html(originalBtnText);
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                if (errors) {
                                    displayErrors(errors);
                                    showErrorMessage('Please fix the errors below.');
                                } else if (xhr.responseJSON.message) {
                                    showErrorMessage(xhr.responseJSON.message);
                                }
                            } else if (xhr.status === 500) {
                                const message = xhr.responseJSON?.message || 'An error occurred while creating the sale. Please try again.';
                                showErrorMessage(message);
                            } else {
                                const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                                showErrorMessage(message);
                            }
                        }
                    });
                });

                // Initial attachment
                attachEventListeners();
                calculateTotals();

                function addSaleItem() {
                    const newItem = `
                    <div class="sale-item" data-item="${itemCount}" style="padding: 1rem; background: #f7fafc; border-radius: 4px; margin-bottom: 1rem;">
                        <div class="grid grid-4" style="gap: 0.75rem; align-items: end;">
                            <div class="form-group" style="margin-bottom: 0; grid-column: span 2;">
                                <label>Product *</label>
                                <select name="products[${itemCount}][product_id]" class="product-select" data-index="${itemCount}" >
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                            data-price="{{ $product->sell_price }}"
                                            data-stock="{{ $product->current_stock }}">
                                            {{ $product->name }} ({{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error-message" id="error-products-${itemCount}-product_id"></span>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Quantity *</label>
                                <input type="number" name="products[${itemCount}][quantity]" min="1" value="1" class="quantity-input" data-index="${itemCount}" >
                                <span class="error-message" id="error-products-${itemCount}-quantity"></span>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Unit Price (৳) *</label>
                                <input type="number" name="products[${itemCount}][unit_price]" step="0.01" class="price-input" readonly data-index="${itemCount}" >
                                <span class="error-message" id="error-products-${itemCount}-unit_price"></span>
                            </div>
                        </div>

                        <div style="margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <div class="stock-info" style="font-size: 0.875rem; color: #718096;"></div>
                                <button type="button" class="btn-remove" data-item="${itemCount}">🗑️ Remove</button>
                            </div>
                            <div class="item-total" style="font-weight: 600; color: #2d3748;">Total: ৳0.00</div>
                        </div>
                    </div>
                `;
                    $('#sale-items').append(newItem);
                    itemCount++;
                    attachEventListeners();
                }

                function attachEventListeners() {
                    // Product selection
                    $('.product-select').off('change').on('change', function () {
                        const $select = $(this);
                        const $option = $select.find('option:selected');
                        const $saleItem = $select.closest('.sale-item');
                        const $priceInput = $saleItem.find('.price-input');
                        const $quantityInput = $saleItem.find('.quantity-input');
                        const $stockInfo = $saleItem.find('.stock-info');

                        if ($option.val()) {
                            const price = parseFloat($option.data('price'));
                            const stock = parseInt($option.data('stock'));

                            $priceInput.val(price.toFixed(2));
                            $quantityInput.attr('max', stock);
                            updateStockInfo($saleItem, stock, parseInt($quantityInput.val()) || 0);
                            calculateItemTotal($saleItem);
                        } else {
                            $priceInput.val('');
                            $quantityInput.removeAttr('max');
                            $stockInfo.html('');
                            $saleItem.find('.item-total').text('Total: ৳0.00');
                        }

                        calculateTotals();
                    });
                    // Quantity change
                    $('.quantity-input').off('input').on('input', function () {
                        const $input = $(this);
                        const $saleItem = $input.closest('.sale-item');
                        const $select = $saleItem.find('.product-select');
                        const $option = $select.find('option:selected');

                        if ($option.val()) {
                            const stock = parseInt($option.data('stock'));
                            const quantity = parseInt($input.val()) || 0;
                            updateStockInfo($saleItem, stock, quantity);
                            calculateItemTotal($saleItem);
                        }

                        calculateTotals();
                    });

                    // Remove button
                    $('.btn-remove').off('click').on('click', function () {
                        const itemId = $(this).data('item');
                        $(`.sale-item[data-item="${itemId}"]`).remove();
                        calculateTotals();
                    });
                }

                function updateStockInfo($saleItem, stock, quantity) {
                    const $stockInfo = $saleItem.find('.stock-info');

                    if (quantity > stock) {
                        $stockInfo.html(`<span class="stock-error">⚠️ Insufficient stock! Available: ${stock}</span>`);
                    } else if (quantity > stock * 0.8) {
                        $stockInfo.html(`<span class="stock-warning">⚡ Low stock! Available: ${stock}</span>`);
                    } else {
                        $stockInfo.html(`Available stock: ${stock}`);
                    }
                }

                function calculateItemTotal($saleItem) {
                    const $priceInput = $saleItem.find('.price-input');
                    const $quantityInput = $saleItem.find('.quantity-input');
                    const $itemTotal = $saleItem.find('.item-total');

                    const price = parseFloat($priceInput.val()) || 0;
                    const quantity = parseInt($quantityInput.val()) || 0;
                    const total = price * quantity;

                    $itemTotal.text(`Total: ৳${total.toFixed(2)}`);
                }

                function calculateTotals() {
                    let subtotal = 0;
                    $('.sale-item').each(function () {
                        const $item = $(this);
                        const price = parseFloat($item.find('.price-input').val()) || 0;
                        const quantity = parseInt($item.find('.quantity-input').val()) || 0;
                        subtotal += price * quantity;
                    });
                    const discount = parseFloat($('#discount_amount').val()) || 0;
                    const vatPercentage = parseFloat($('#vat_percentage').val()) || 0;
                    const afterDiscount = Math.max(0, subtotal - discount);
                    const vatAmount = (afterDiscount * vatPercentage) / 100;
                    const total = afterDiscount + vatAmount;
                    const paidAmount = parseFloat($('#paid_amount').val()) || 0;
                    const change = paidAmount - total;

                    // Update summary display
                    $('#summary-subtotal').text(`৳${subtotal.toFixed(2)}`);
                    $('#summary-after-discount').text(`৳${afterDiscount.toFixed(2)}`);
                    $('#summary-vat').text(`৳${vatAmount.toFixed(2)}`);
                    $('#summary-total').text(`৳${total.toFixed(2)}`);

                    // Update change/due
                    const $changeDue = $('#summary-change');
                    if (change >= 0) {
                        $changeDue.text(`৳${change.toFixed(2)}`).css('color', '#2f855a');
                        $('#change-due').css('background', '#f0fff4');
                    } else {
                        $changeDue.text(`৳${Math.abs(change).toFixed(2)} (Due)`).css('color', '#c53030');
                        $('#change-due').css('background', '#fff5f5');
                    }
                }

                function clearErrors() {
                    $('.error-message').text('');
                    $('.form-group input, .form-group select, .form-group textarea').removeClass('error');
                    $('#alert-container').empty();
                }

                function displayErrors(errors) {
                    $.each(errors, function (field, messages) {
                        const errorId = 'error-' + field.replace(/\./g, '-');
                        const $errorElement = $('#' + errorId);
                        let $inputElement = $('[name="' + field + '"]');
                        if (!$inputElement.length) {
                            const arrayNotation = field.replace(/\.(\d+)\./g, '[$1][');
                            $inputElement = $('[name="' + arrayNotation + '"]');
                        }

                        if ($errorElement.length) {
                            $errorElement.text(messages[0]);
                        }
                        if ($inputElement.length) {
                            $inputElement.addClass('error');
                        }
                    });
                    const $firstError = $('.error-message:not(:empty)').first();
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 100
                        }, 500);
                    }
                }

                function showSuccessMessage(message) {
                    const $banner = $(`
                    <div class="alert-banner alert-success">
                        <div class="alert-icon">✓</div>
                        <div class="alert-content">
                            <div class="alert-title">Success</div>
                            <div>${message}</div>
                        </div>
                    </div>
                `);

                    $('#alert-container').html($banner);
                    $('html, body').animate({ scrollTop: 0 }, 300);
                    const $toast = $('<div></div>')
                        .addClass('alert alert-success')
                        .text(message)
                        .css({
                            position: 'fixed',
                            top: '20px',
                            right: '20px',
                            zIndex: '9999',
                            minWidth: '300px'
                        });

                    $('body').append($toast);
                    setTimeout(function () { $toast.remove(); }, 3000);
                }

                function showErrorMessage(message) {
                    const $banner = $(`
                    <div class="alert-banner alert-error">
                        <div class="alert-icon">⚠</div>
                        <div class="alert-content">
                            <div class="alert-title">Error</div>
                            <div>${message}</div>
                        </div>
                        <div class="alert-close" onclick="this.parentElement.remove()">×</div>
                    </div>
                `);

                    $('#alert-container').html($banner);
                    $('html, body').animate({ scrollTop: 0 }, 300);
                    const $toast = $('<div></div>')
                        .addClass('alert alert-error')
                        .text(message)
                        .css({
                            position: 'fixed',
                            top: '20px',
                            right: '20px',
                            zIndex: '9999',
                            minWidth: '300px'
                        });

                    $('body').append($toast);
                    setTimeout(function () { $toast.remove(); }, 5000);
                }
            });
        </script>
    @endpush
@endsection