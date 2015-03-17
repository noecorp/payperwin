define([], () ->
	
	# Utility functions
	return {
		foo: () ->
			return 'bar'

		csrfToken: () ->

			if $('input[name="_token"]').first()?
				return $('input[name="_token"]').first().val()
			else
				return null

	}

)