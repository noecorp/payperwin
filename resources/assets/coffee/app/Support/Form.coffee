$ = jQuery

define(['app/Support/Request'], (Request) ->

	Form (id) ->
		self = this

		working = false

		$el = $('#'+id)

		$submit = $($el.find('[app-submit]').get(0))

		$el.submit((e) ->
			e.preventDefault()

			return false if working

			working = true

			$submit.prop('disabled',true) if $submit

			values = {}

			for key,value of $el.serializeArray()
				values[value.name] = value.value

			request = new Request()

			request.setMethod($el.attr('method'))

			request.setUrl($el.attr('action'))

			request.setData(values)

			request.setError((error, statusCode) ->
				console.log(status,error)

				$submit.prop('disabled',false) if $submit
			)

			request.setSuccess((data, statusCode) ->
				console.log(data)
			)

			request.start()
		)

	return Form
)