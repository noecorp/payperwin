define([], () ->

	##
	## Creates a new server request object.
	##
	## @class
	##
	## @param {string} method - The type of method (GET,POST,PUT,DELETE).
	## @param {string} url - The URL that the request calls.
	## @param {Object} data - Key-value query parameters to add to the request.
	##
	Request = () ->

		self = this

		method = 
		url = 
		data = null

		##
		## 
		##
		## @callback errorCallback
		##
		errorCallback =
		successCallback = 
		alwaysCallback = () ->

		##
		## Set request error callback.
		##
		## @param {errorCallback}
		## @return {Request}
		##
		this.setError = (callback) -> 
			errorCallback = callback
			return self

		##
		## Set request success callback.
		##
		## @param {successCallback}
		## @return {Request}
		##
		this.setSuccess = (callback) ->
			successCallback = callback
			return self

		this.setAlways = (callback) ->
			alwaysCallback = callback
			return self

		this.setMethod = (m) ->
			method = m
			return self

		this.setUrl = (u) ->
			url = u
			return self

		this.setData = (d) ->
			data = d
			return self

		##
		## Start the request and trigger callbacks when appropriate.
		##
		this.start = () ->
			$.ajax({
				type: method,
				url: url,
				data: data
			}).done((data,textStatus,jqXHR) ->
				successCallback(data, jqXHR.status)
			).fail((jqXHR, textStatus, errorThrown) ->
				errorCallback(jqXHR.responseJSON.error, jqXHR.status)
			).always(() ->
				alwaysCallback()
			)

	return Request
)