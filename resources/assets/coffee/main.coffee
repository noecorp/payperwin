$ = jQuery

# RequireJS logic
define([], () ->

    'use strict'

    # A temporary solution: we need a way to pull and match the individual js
    # files with their versioned equivalents from Elixir's rev-manifest file.
    $.ajax({
        dataType: 'json',
        url: '/build/rev-manifest.json',
        success: (data) ->
            paths = {}

            for key,value of data
                paths[key.replace(/^js\/(.*)\.js$/,'$1')] = value.replace(/(.*).js$/,'/build/$1') if (/^js\/app\//.test(key))
            
            # Configure require.js paths and shims
            require.config({
                baseUrl: '/js/vendor',
                paths: paths
            })

            # Load the router
            require(['domReady','router'], (domReady, router) ->
                domReady ->
                    router.registerRoutes({

                        # matches an exact path
                        register: { path: '/register', moduleId: 'app/Routes/Register' }

                        # matches using a wildcard
                        # customer: { path: '/customer/*', moduleId: 'customer/customerView' },

                        # matches using a path variable
                        # order: { path: '/orders/:id', moduleId: 'order/orderView' },

                        # matches a pattern like '/word/number'
                        # regex: { path: /^\/\w+\/\d+$/i, moduleId: 'regex/regexView' },

                        # matches everything else
                        notFound: { path: '*', moduleId: 'app/Routes/Register' }

                    })
                    .on('routeload', (module, routeArguments) ->

                        body = document.querySelector('body');

                    )
                    .init() # Set up event handlers and trigger the initial page load
            )
    })
)