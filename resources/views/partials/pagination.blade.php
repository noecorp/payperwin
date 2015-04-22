<?php $url = (strstr($url,'?') === false) ? $url . '?page=' : $url . '&page='; $page = (isset($_GET['page'])) ? $_GET['page'] : 1; ?>
<div class="pagination">
	<ul>
		<li class="previous {{ (!$less) ? 'disabled' : '' }}"><a title="Next" href="{{ ($less) ? $url . ($page - 1) : 'javascript:;' }}" class="fui-arrow-left"></a></li>
		<li class="next {{ (!$more) ? 'disabled' : '' }}"><a title="Previous" href="{{ ($more) ? $url . ($page + 1) : 'javascript:;' }}" class="fui-arrow-right"></a></li>
	</ul>
</div>
