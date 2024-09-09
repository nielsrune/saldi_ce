<?php

function build_url($page, $step) {
	// Parse the current URL
	$url = $_SERVER['REQUEST_URI'];
	$parsedUrl = parse_url($url);
	parse_str($parsedUrl['query'], $queryParams);

	// Update the start and linjeantal parameters
	$queryParams['start'] = $page * $step+1;
	$queryParams['linjeantal'] = $step;

	// Rebuild the query string with the updated parameters
	$newQuery = http_build_query($queryParams);

	// Rebuild the full URL
	return $parsedUrl['path'] . '?' . $newQuery;
}

function pagination($start, $step, $max) {
	/*echo "start".$start;
	echo "<br>";
	echo "step".$step;
	echo "<br>";
	echo "max".$max;
	echo "<br>";*/

	#######

	$pages = ceil($max / $step);
	$currentPage = ceil($start / $step)-1;

	if ($currentPage != 0) $back = build_url($currentPage - 1, $step);
	else $back = build_url(0, $step);

	if ($currentPage != $pages-1) $forward = build_url($currentPage + 1, $step);
	else $forward = "";

	$plainstyle = "padding: 6px 12px; border: 0; background-color: #ddd; cursor: pointer; width: 31px;";
	$minpage = build_url(0, $step);
	$maxpage = build_url($pages-1, $step);

	print "<div style='display: inline-flex; gap: 4px; align-items: center; padding-bottom: 5px; '>";
	print "<span>Rækker per side:</span>";
	print "<select class='pagination-selector'>";

	if ($step != 100) print "<option>100</option>";
	else print "<option selected>100</option>";
	if ($step != 50) print "<option>50</option>";
	else print "<option selected>50</option>";
	if ($step != 20) print "<option>20</option>";
	else print "<option selected>20</option>";

	print "</select>";
	$diff = $start + $step-1;
	$diffroof = min($max, $start+$diff);
	print "<span style='padding: 0 10px'>$start-$diffroof af $max</span>";
	print "<a href='$minpage'><button type='button' style='$plainstyle'>«</button></a>";
	print "<a href='$back'><button type='button' style='$plainstyle'><</button></a>";
	# Pages, removed for easier use
	for ($i = 0; $i < $pages; $i++) {
		if (abs($i - $currentPage) < 3 ) {
			$page = $i + 1;
			$newUrl = build_url($i, $step);

			$style = "  padding: 6px 2px; border: 0; cursor: pointer; width: 31px;";
			if ($i == $currentPage) {
				$style .= "background-color: #1a55a9; color: white;";
			} else {
				$style .= "background-color: #ddd";
			}

			print "<a href='$newUrl'><button type='button' style='$style'>$page</button></a>";
		}
	}
	print "<a href='$forward'><button type='button' style='$plainstyle'>></button></a>";
	print "<a href='$maxpage'><button type='button' style='$plainstyle'>»</button></a>";
	print "</div>";

	print "<script>
	document.addEventListener('DOMContentLoaded', function() {
		var buttons = document.querySelectorAll('.pagination-selector');

		// Add event listener to each button
		buttons.forEach(function(button) {
			button.addEventListener('change', function(event) {
				console.log(event);
				var select = event.target;
				var linjeantal = select.value;
				var url = new URL(window.location.href);
				url.searchParams.set('linjeantal', linjeantal);
				window.location.href = url.toString();
			});
		});
	});

	</script>";
}
?>
