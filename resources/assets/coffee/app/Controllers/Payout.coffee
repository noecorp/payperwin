class Payout extends Controller
	actions: {
		index: (parameters) =>
			
			$form = $('#payout-form')

			paypalPercent = 2.9
			paypalFlat = 0.30
			ppwPercent = parseFloat($form.data('ppw-percent'))
			ppwFlat = parseFloat($form.data('ppw-flat'))

			$amount = $('#payout-amount')

			calc = () ->
				val = parseFloat($amount.val())

				if val > 0

					val1 = val / 100 * paypalPercent + paypalFlat

					$('#payout-net-paypal').text('$' + val1.toFixed(2))

					val2 = (val - val1) / 100 * ppwPercent + ppwFlat

					$('#payout-net-ppw').text('$' + val2.toFixed(2))

					val3 = val - val1 - val2

					$('#payout-total').text('$' + val3.toFixed(2))

				else

					$('#payout-net-paypal').text('-')
					$('#payout-net-ppw').text('-')
					$('#payout-total').text('-')

			$amount.on('change keyup paste', () ->
				calc()
			)

			$amount.focus()
			$amount.parent().addClass('focus')

			calc()
	}

window.app.route('payout','payout.index')
window.app.controller('payout',Payout)
