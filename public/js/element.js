jQuery(document).ready(function($) {
	let stripe = Stripe( emepStripe.pubKey );
	let elements = stripe.elements();

	let style = {
		base: {
			color: '#32325d',
			fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
			fontSmoothing: 'antialiased',
			fontSize: '16px',
			'::placeholder': {
				color: '#aab7c4'
			}
		},
		invalid: {
			color: '#fa755a',
			iconColor: '#fa755a'
		}
	};

	let card = elements.create('card', {style: style});
	card.mount('#emep-card-element');

	card.addEventListener('change', function(event) {
		let displayError = document.getElementById('emep-card-errors');
		if (event.error) {
			displayError.textContent = event.error.message;
		} else {
			displayError.textContent = '';
		}
	});

	// Form submission handler
	const form = document.getElementById('emep-payment-form');
	const submitButton = document.getElementById('emep_payment_form_submitt_button');
	const processingSpinner = document.getElementById('emep_payment_form_spinner');

	// Example POST method implementation:
	async function postData(url = '', data = {}) {
		return await $.post(
			emepStripe.ajaxurl, 
			data, 
			function(data, textStatus, xhr) {}
		);
	}

	let requiredFields = $('input.emep-payment-form-input[required]');

	requiredFields.change(function(event) {
		let target = $(event.target);
		if ( '' != target.val() ) {
			target.removeClass('is-required');
		}
	});

	form.addEventListener('submit', async function(event) {
		
		event.preventDefault();
		submitButton.disabled = true;
		form.classList.add('processing');

		let incomplete = false;

		// Make sure required fields are filled in
		await $.each(requiredFields, function(index, element) {
			let el = $(element);
			if ( '' == el.val() ) {
				el.addClass('is-required');
				incomplete = true;
			}
		});

		// Bail if payment form in incomplete
		if ( true === incomplete ) {
			form.classList.remove('processing');
			submitButton.disabled = false;
			return;
		}

		// Create payment intent
		let response = await postData( emepStripe.ajaxurl, {
			action: 'emep_ajax_get_payment_intent',
			nonce: emepStripe.nonce,
			amount: emepStripe.amount,
			description: emepStripe.description,
			post_id: emepStripe.post_id,
			name: document.getElementById('emep_payment_name').value,
			email: document.getElementById('emep_payment_email').value,
			user_id: emepStripe.user_id
		} );

		let name = document.getElementById('emep_payment_name');
		let userID = document.getElementById('emep_payment_user_id');
		let paymentIntentID = document.getElementById('emep_payment_intent_id');
		userID.value = response.data.user_id;

		stripe.confirmCardPayment(response.data.intent_client_secret, {
			payment_method: {
				card: card,
				billing_details: {
					name: name.value
				}
			}
		}).then(function(result) {
			if (result.error) {
				form.classList.remove('processing');
				submitButton.disabled = false;
				console.log(result.error.message);
			} else {
				// The payment has been processed!
				if (result.paymentIntent.status === 'succeeded') {
					submitButton.style.display = 'none';
					paymentIntentID.value = result.paymentIntent.id;
					form.submit();
				}
			}
		});
	});
});