<?php
// Enqueue Alpine.js
function enqueue_alpine_js()
{
    wp_enqueue_script('alpine-js', 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_alpine_js');

// Shortcode to render payment form
function render_payment_form()
{
    ob_start(); ?>
    <div x-data="paymentForm()" id="payment-form" style="position: relative;">
        <div class="overlay" x-show="isSubmitting">
            <div class="spinner"></div>
        </div>
        <div x-show="paymentSuccessful" class="success-message">
            <h2>Payment successful!</h2>
        </div>
        <form x-on:submit.prevent="submitPayment" x-show="!paymentSuccessful">

            <div class="form-control row">
                <label for="first_name">
                    <input type="text" id="first_name" placeholder="First Name" x-model="firstName" maxlength="50" required>
                    <span x-show="firstNameError" class="invalid">Required</span>
                </label>
                <label for="last_name">
                    <input type="text" id="last_name" placeholder="Last Name" x-model="lastName" maxlength="50" required>
                    <span x-show="lastNameError" class="invalid">Required</span>
                </label>
            </div>

            <div class="form-control row">
                <label for="card_number" style="flex-grow: 1;">
                    <input type="text" id="card_number" placeholder="Debit/Credit Card Number" x-model="cardNumber" x-on:focus="cardNumberError = false" :class="{'invalid': cardNumberError}" maxlength="19" required>
                    <span x-show="cardNumberError" class="invalid">Invalid card number</span>
                </label>
                <label for="cvv" style="width: 20%;">
                    <input type="text" id="cvv" placeholder="CVV" x-model="cvv" maxlength="4" required>
                    <span x-show="cvvError" class="invalid">Invalid CVV</span>
                </label>
            </div>

            <div class="form-control row">
                <label for="expiration_date">
                    <input type="text" id="expiration_date" placeholder="mm/yy" x-model="expirationDate" x-on:focus="expirationDateError = false" :class="{'invalid': expirationDateError}" data-error="Invalid expiration date" maxlength="5" required>
                    <span x-show="expirationDateError" class="invalid">Invalid exp (mm/yy)</span>
                </label>
                <label for="amount">
                    <input placeholder="Payment Amount" type="number" id="amount" x-model="amount" :class="{'invalid': amountError}" data-error="Invalid amount" required>
                    <span x-show="amountError" class="invalid">Invalid amount</span>
                </label>
            </div>

            <button :disabled="isSubmitting">Submit Payment</button>
        </form>
    </div>

    <script>
        function paymentForm() {
            return {
                firstName: '',
                lastName: '',
                cvv: '',
                cardNumber: '',
                expirationDate: '',
                amount: '',
                isSubmitting: false,
                firstNameError: false,
                lastNameError: false,
                cardNumberError: false,
                expirationDateError: false,
                amountError: false,
                paymentSuccessful: false,
                async submitPayment(e) {
                    this.isSubmitting = true;
                    console.log('submitting...');
                    e.preventDefault();

                    this.resetErrors();

                    if (!this.validateForm()) {
                        this.isSubmitting = false;
                        return;
                    }

                    const data = await (await fetch('<?php echo esc_url(rest_url('wp/v2/submit-payment')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        body: JSON.stringify({
                            first_name: this.firstName,
                            last_name: this.lastName,
                            card_number: this.cardNumber,
                            cvv: this.cvv,
                            expiration_date: this.expirationDate,
                            amount: this.amount
                        })
                    })).json()

                    this.isSubmitting = false;
                    if (data.success) {
                        this.paymentSuccessful = true;
                    } else {
                        alert('Payment failed: ' + data.message);
                    }

                },
                validateForm() {
                    let isValid = true;
                    const expirationDatePattern = /^(0[1-9]|1[0-2])\/\d{2}$/;
                    const cvvPattern = /^[0-9]{3,4}$/;

                    if (!this.firstName || !this.lastName) {
                        console.log('First name and last name are required');
                        isValid = false;
                    }
                    if (!this.luhnCheck(this.cardNumber)) {
                        console.log('Invalid card number');
                        this.cardNumberError = true;
                        isValid = false;
                    }
                    if (!cvvPattern.test(this.cvv)) {
                        console.log('Invalid CVV');
                        this.cvvError = true;
                        isValid = false;
                    }
                    if (!expirationDatePattern.test(this.expirationDate)) {
                        console.log('Invalid expiration date');
                        this.expirationDateError = true;
                        isValid = false;
                    }
                    if (this.amount <= 0) {
                        console.log('Invalid amount');
                        this.amountError = true;
                        isValid = false;
                    }

                    // Currency format for this.amount
                    this.amount = parseFloat(this.amount).toFixed(2);

                    return isValid;
                },
                resetErrors() {
                    this.cardNumberError = false;
                    this.expirationDateError = false;
                    this.amountError = false;
                },
                luhnCheck(cardNumber) {
                    let sum = 0;
                    for (let i = 0; i < cardNumber.length; i++) {
                        let intVal = parseInt(cardNumber.substr(i, 1));
                        if (i % 2 == 0) {
                            intVal *= 2;
                            if (intVal > 9) {
                                intVal = 1 + (intVal % 10);
                            }
                        }
                        sum += intVal;
                    }
                    return (sum % 10) == 0;
                }
            }
        }
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('payment-form', 'render_payment_form');

// Register REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('wp/v2', '/submit-payment', [
        'methods' => 'POST',
        'callback' => 'handle_payment_submission',
        'permission_callback' => '__return_true',
    ]);
});

function handle_payment_submission(WP_REST_Request $request)
{
    $firstName = sanitize_text_field($request->get_param('first_name'));
    $lastName = sanitize_text_field($request->get_param('last_name'));
    $cardNumber = sanitize_text_field($request->get_param('card_number'));
    $expirationDate = sanitize_text_field($request->get_param('expiration_date')); // Ensure YYYY-MM format
    $expirationDateParts = explode('/', $expirationDate);

    if (count($expirationDateParts) === 2) {
        $expirationMonth = $expirationDateParts[0];
        $expirationYear = '20' . $expirationDateParts[1];
        $expirationDate = $expirationYear . '-' . $expirationMonth;
    } else {
        return new WP_Error('invalid_data', 'Invalid expiration date format', ['status' => 400]);
    }

    $amount = floatval($request->get_param('amount'));
    $cvv = sanitize_text_field($request->get_param('cvv')); // Add CVV as a parameter

    if (!$firstName || !$lastName || !$cardNumber || !$expirationDate || !$amount || $amount <= 0 || !$cvv) {
        return new WP_Error('invalid_data', 'Invalid payment data', ['status' => 400]);
    }

    // Authorize.net credentials
    $api_login_id = '7pD5tGQ2mc'; // Use environment variables for security
    $transaction_key = '6Hj793NmBu75p7rT';
    $endpoint = 'https://apitest.authorize.net/xml/v1/request.api'; // Sandbox endpoint
    // $endpoint = 'https://api.authorize.net/xml/v1/request.api'; // Sandbox endpoint

    $data = [
        'createTransactionRequest' => [
            'merchantAuthentication' => [
                'name' => $api_login_id,
                'transactionKey' => $transaction_key,
            ],
            'transactionRequest' => [
                'transactionType' => 'authCaptureTransaction',
                'amount' => number_format($amount, 2, '.', ''), // Format to two decimal places
                'payment' => [
                    'creditCard' => [
                        'cardNumber' => $cardNumber,
                        'expirationDate' => $expirationDate,
                        'cardCode' => $cvv, // Use CVV provided by client
                    ],
                ],
                'billTo' => [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                ],
            ],
        ],
    ];

    $response = wp_remote_post($endpoint, [
        'body' => json_encode($data),
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('payment_failed', 'Payment failed due to connection error', ['status' => 500]);
    }

    $body = wp_remote_retrieve_body($response);

    // Remove BOM if present
    $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

    $result = json_decode($body, true);

    if (isset($result['messages']['resultCode']) && $result['messages']['resultCode'] === 'Ok') {
        return ['success' => true, 'transaction_id' => $result['transactionResponse']['transId'] ?? null];
    } else {
        $error_code = $result['messages']['message'][0]['code'] ?? '-1';
        $error_message = $result['messages']['message'][0]['text'] ?? 'Unknown error occurred';
        $error_message .= ' (' . $error_code . ')';
        return new WP_Error('payment_failed', 'Payment Failed: ' . $error_message, ['status' => 500, 'response' => $result]);
    }
}

?>