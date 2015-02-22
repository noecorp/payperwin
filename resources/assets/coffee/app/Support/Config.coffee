define([], () ->
	
	Config = () ->
		config = {
			foo : {
				bar : 'baz'
			}
		}

		# Allow dot-syntax nesting of keys.
		this.get = (key) ->
			keys = key.split('.')

			c = config

			loop
				k = keys.shift()
				c = c[k]
				break unless (keys.length)

			return c

	return Config
)