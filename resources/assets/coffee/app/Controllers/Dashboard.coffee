class Dashboard extends Controller
	actions: {
		index: (parameters) =>

			options = {
				axisX: {
					showGrid: false,
					showLabel: true
				},

				axisY: {
					showGrid: true,
					showLabel: true,
					labelInterpolationFnc: (value) ->
						return '$' + value.toFixed(2)
				},

				fullWidth: false,

				chartPadding: {
					right: 20
				},

				lineSmooth: false,

				low: 0
			}

			responsiveOptions = [
				['screen and (min-width: 768px)', {
					axisX: {
						showGrid: false
					},

					axisY: {
						showGrid: true
					}
				}]
			]

			$earningsChart = $('#earnings-chart')

			data = {
				labels: $earningsChart.data('labels').split(','),
				series: [
					{
						data: $earningsChart.data('values').split(','),
						className: 'ct-success'
					}
				]
			}

			new Chartist.Line('#earnings-chart', data, options, responsiveOptions)

			$spendingChart = $('#spending-chart')

			data = {
				labels: $spendingChart.data('labels').split(','),
				series: [
					{
						data: $spendingChart.data('values').split(','),
						className: 'ct-success'
					}
				]
			}

			new Chartist.Line('#spending-chart', data, options, responsiveOptions)


			$charts = $('.ct-chart')

			$toolTip = $charts.append('<div class="chart-tooltip""></div>').find('.chart-tooltip').hide()

			$charts.on('mouseenter', '.ct-point', () ->
				$point = $(this)
				value = $point.attr('ct:value')
				seriesName = $point.parent().attr('ct:series-name')
				$point.closest('.ct-chart').find('.chart-tooltip').html('$' + value).show()
			)

			$charts.on('mouseleave', '.ct-point', () ->
				$point = $(this)
				$point.closest('.ct-chart').find('.chart-tooltip').hide()
			)

			$charts.on('mousemove', (event) ->
				$point = $(this)
				$point.closest('.ct-chart').find('.chart-tooltip').css({
					left: (event.offsetX || event.originalEvent.layerX) - $toolTip.width() / 2 - 10,
					top: (event.offsetY || event.originalEvent.layerY) - $toolTip.height() - 40
				})
			)

	}

window.app.route('dashboard','dashboard.index')
window.app.controller('dashboard',Dashboard)
