define(['./Base','stripe'], (Base,Stripe) ->
	Controller = new Base('DepositsController')

	Controller.render = () ->
		Stripe.setPublishableKey('pk_test_6pRNASCoBOKtIshFeQd4XMUh')

		$form = $('#payment-form')

		stripeResponseHandler = (status, response) ->
			console.log(response,status)

			if response.error
				# Show the errors on the form
				$form.find('.payment-errors').text(response.error.message)
				$form.find('button').prop('disabled', false)
			else
				# response contains id and card, which contains additional card details
				token = response.id

				# Copy last 4 digits of CC for reference only
				lastFour = $form.find('[data-stripe="number"]').val()
				lastFour = lastFour.substring(lastFour.length - 4)

				# Insert the token into the form so it gets submitted to the server
				$form.append($('<input type="text" name="stripeToken" />').val(token))
				$form.append($('<input type="text" name="cardRef" />').val(lastFour))
			
			return
			
			# and submit
			#$form.get(0).submit()

		$form.submit((event) ->
			# Disable the submit button to prevent repeated clicks
			$form.find('button').prop('disabled', true)

			Stripe.card.createToken($form, stripeResponseHandler)

			# Prevent the form from submitting with the default action
			return false
		)

	return Controller
)