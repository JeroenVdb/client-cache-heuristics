<?php
$cachebusterString = '';
$currentUrl = 'http://' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];
if ($_GET['cachebuster']) {
	$cachebusterString = '?cachebuster=' . $_GET['cachebuster'];
	$newCachebusterUrl = 'http://jeroenvdb.be/check-resource-origin/' . '?cachebuster=' . (intval($_GET['cachebuster']) + 1);
} else {
	$newCachebusterUrl = $currentUrl . '?cachebuster=1';
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Testing Resource caching heuristics</title>

	<link rel="stylesheet" href="http://jeroenvdb.be/check-resource-origin/style.css<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-css-1">
</head>
<body>
	<a href="<?php echo $currentUrl ?>">Soft refresh this page (like navigating forward forward or forward backward)</a> -
	<a href="<?php echo $newCachebusterUrl ?>">Soft refresh with new image (I use cachebuster for now)</a> -
	<a href="https://github.com/JeroenVdb/client-cache-heuristics">Github</a>
	<hr/>

<pre class="fjs-results"><b>General Log</b><br /></pre>
<pre class="logger-css-1"><b>Head items log</b><br /></pre>

	<hr/>
	<img src="http://jeroenvdb.be/check-resource-origin/img-1.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-1" />
	<pre class="logger-img-1"></pre>
	<img src="http://jeroenvdb.be/check-resource-origin/img-2.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-2" />
	<pre class="logger-img-2"></pre>
	<img src="http://jeroenvdb.be/check-resource-origin/img-3.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-3" />
	<pre class="logger-img-3"></pre>
	<img src="http://jeroenvdb.be/check-resource-origin/img-4.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-4" />
	<pre class="logger-img-4"></pre>
	<img src="http://jeroenvdb.be/check-resource-origin/img-5.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-5" />
	<pre class="logger-img-5"></pre>
	<img src="http://jeroenvdb.be/check-resource-origin/img-6.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-6" />
	<pre class="logger-img-6"></pre>
	<img src="http://jeroenvdb.be/check-resource-origin/img-7.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-7" />
	<pre class="logger-img-7"></pre>
	<img src="http://jeroenvdb.be/check-resource-origin/img-8.jpg<?php echo $cachebusterString ?>" class="fjs-resource" data-logger=".logger-img-8" />
	<pre class="logger-img-8"></pre>
	<h2>Notes</h2>
	<pre>
Spec for transferSize: https://w3c.github.io/resource-timing/#dom-performanceresourcetiming-transfersize
Remember: will be 0 for CORS resources unless Timing-Allow-Origin header (https://w3c.github.io/resource-timing/#timing-allow-check)
	</pre>

	<script>
		window.onload = init();

		function init() {
			window.checkResourceTimingData = window.setInterval(function() {
				if (performance.getEntries().length) {
					window.clearInterval(checkResourceTimingData);

					document.querySelectorAll('.fjs-resource').forEach(function(element) {
						getResourceCacheData(element);
					});
				}
			}, 100);
		}

		window.console.log2 = function(toLog, logElement) {
			if (typeof toLog === 'object') {
				toLog = JSON.stringify(toLog, null, 4);
			}

			if (logElement) {
				logElement.innerHTML += toLog + '<br />';
			} else {
				document.querySelector('.fjs-results').innerHTML += toLog + '<br />';
			}
		}

		function getResourceCacheData(element) {
			var entryNameToTest = element.src || element.href;

			var logElement = document.querySelector(element.dataset.logger);

			console.log2('Test resourceName: ' + entryNameToTest);
			console.log2('ResourceName: ' + entryNameToTest, logElement);

			if (window.performance && window.performance.getEntriesByName) {
				if (performance.getEntriesByName(entryNameToTest).length) {
					var entry = performance.getEntriesByName(entryNameToTest)[0];
					checkResourceOrigin(entry, logElement);
				}
			} else {
				reportOldBrowser();
			}
		}

		function checkResourceOrigin(PerformanceResourceTimingObject, logElement) {
			var entry = PerformanceResourceTimingObject;
			var transferSize, duration;

			transferSize = (typeof entry.transferSize !== 'undefined') ? entry.transferSize : null;
			duration = (typeof entry.duration !== 'undefined') ? entry.duration : null;
			encodedBodySize = (typeof entry.encodedBodySize !== 'undefined') ? entry.encodedBodySize : null;

			if (transferSize === null || duration === null) {
				reportOldBrowser();
				return;
			}

			console.log2(entry, logElement);
			console.log2('transferSize: ' + transferSize, logElement);
			console.log2('duration: ' + duration, logElement);
			console.log2('encodedBodySize: ' + encodedBodySize, logElement);

			/*
				THE MAGIC? PLEASE GIVE FEEDBACK: vandenberghe.jeroen@gmail.com / @jrnvdb
			*/

			if (transferSize === 0 && duration === 0) {
				console.log2('FROM BROWSER CACHE (MEMORY CACHE) (200)', logElement);
			} else if (transferSize === 0 && duration > 0) {
				console.log2('FROM BROWSER CACHE (DISK CACHE) (200)', logElement);
			} else if (transferSize > 0 && transferSize < encodedBodySize) {
				console.log2('FROM BROWSER CACHE AFTER VALIDATION (304)', logElement);
			} else {
				console.log2('FROM SERVER, NO CACHE (200)', logElement);
			}

			console.log2('-------------------------------', logElement);
		}

		function reportOldBrowser() {
			console.log2('Your browser is incompatible, use Chrome 54 or up');
		}
	</script>
</body>
</html>
