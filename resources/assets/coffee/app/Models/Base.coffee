define(() ->
	ModelBase = (title) ->
		this.title = title

	ModelBase.prototype = {
		getTitle: () ->
			return this.title
	}

	return ModelBase
)