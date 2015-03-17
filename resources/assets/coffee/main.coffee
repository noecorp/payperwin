$ = jQuery

# RequireJS logic
define([], () ->

    'use strict'

    # A temporary (?) solution: we need a way to pull and match the individual js
    # files with their versioned equivalents from Elixir's rev-manifest file.
    $.ajax({
        dataType: 'json',
        url: '/build/rev-manifest.json',
        success: (data) ->
            paths = {}

            for key,value of data
                paths[key.replace(/^js\/(.*)\.js$/,'$1')] = value.replace(/(.*).js$/,'/build/$1') if (/^js\/app\//.test(key))

            paths['stripe'] = 'https://js.stripe.com/v2/?1'; # The ?1 prevents RequireJS attaching .js
            
            # Configure paths and shims
            require.config({
                baseUrl: '/js/vendor',
                paths: paths,
                shim: {
                    'stripe': {
                        exports: 'Stripe'
                    }
                }
            })

            # Load the router
            require(['domReady','router'], (domReady, router) ->
                domReady ->
                    router.registerRoutes({

                        authAction: { path: '/auth/:action', moduleId: 'app/Routes/Auth' },

                        depositsAction: { path: '/deposits/:action', moduleId: 'app/Routes/Deposits' },

                        usersId: { path: '/users/:id', moduleId: 'app/Routes/Users' },
                        usersIdAction: { path: '/users/:id/:action', moduleId: 'app/Routes/Users' },

                    })
                    .on('routeload', (module, routeArguments) ->

                        # Run the route logic
                        module.go(routeArguments)

                    )
                    .init() # Set up event handlers and trigger the initial page load
            )
    })
)