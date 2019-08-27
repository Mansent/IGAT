/**
 * This script contains the logging functionality for igat.
 * It collects all required data and sends them to the server
 * when the student leaves the page.
 */

define(['jquery'], function($) {
		var loadTime = new Date($.now())
 
    return {
        init: function() {
					loadTime = new Date($.now());
					$(window).on("beforeunload", function(e) {
						var leaveTime = new Date($.now());
						
						//configure logging request
						let url = "/blocks/igat/ajax.php";
						let urlParams = new URLSearchParams(window.location.search);
						let courseId = urlParams.get('courseid');
						
						//prepare sending data
						let data = new FormData();
						data.append('loadtime', loadTime.getTime());
						data.append('url', window.location.href);
						data.append('leavetime', leaveTime.getTime());
						data.append('destination', document.activeElement.href);
						data.append('courseid', courseId);
						
						// Send the beacon
						let status = navigator.sendBeacon(url, data);

						// Log the data and result
						console.log("sendBeacon: URL = ", url, "; data = ", data, "; status = ", status);
					});
        }
    };
});