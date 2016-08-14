<script type="text/javascript">
<!--
/*
 *   Copyright (c) 2008 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 */

//
// "WhereAmI?" Custom Control and Google Map print out functions.
//
// $Id: yb_plugin_html_gmap.js 433 2008-11-09 08:55:01Z msakamoto-sf $
//

function WhereAmIControl() {}

function ybjs_build_whereami() {
	WhereAmIControl.prototype = new google.maps.Control();

	WhereAmIControl.prototype.initialize = function(map) {
		var container = document.createElement("div");
		var mainDiv = document.createElement("div");
		mainDiv.style.textDecoration = "underline";
		mainDiv.style.color = "#0000cc";
		mainDiv.style.backgroundColor = "white";
		mainDiv.style.font = "small Arial";
		mainDiv.style.border = "1px solid black";
		mainDiv.style.padding = "2px";
		mainDiv.style.marginBottom = "3px";
		mainDiv.style.textAlign = "center";
		mainDiv.style.width = "6em";
		mainDiv.style.cursor = "pointer";
		container.appendChild(mainDiv);
		mainDiv.appendChild(document.createTextNode("WhereAmI?"));
		google.maps.Event.addDomListener(mainDiv, "click", function() {
			var latlng = map.getCenter();
			void(window.prompt('latitude, longitude', latlng.toUrlValue()));
		});

		map.getContainer().appendChild(container);
		return container;
	}

	WhereAmIControl.prototype.getDefaultPosition = function() {
		return new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(7, 20));
	}
}

function ybjs_make_gmap(element_id, lat, lng, zoom, use_small, use_large, use_maptype, use_where, draggable, show_marker, pinmarkmsg) {
google.load("maps", "2", {"callback" : function() {
	if (!google.maps.BrowserIsCompatible()) {
		window.alert('Browser is not compatible for Google Maps');
		return;
	}
	var map_element = $("#" + element_id)[0];
	if (!map_element) {
		window.alert('Specified element[' + element_id + '] was not found.');
		return;
	}
	var map = new google.maps.Map2(map_element);
	map.setCenter(new google.maps.LatLng(lat, lng), zoom);
	if (use_small) { map.addControl(new google.maps.SmallMapControl()); }
	if (use_large) { map.addControl(new google.maps.LargeMapControl()); }
	if (use_maptype) { map.addControl(new google.maps.MapTypeControl()); }
	if (use_where) { map.addControl(new WhereAmIControl()); }
	if (!draggable) { map.disableDragging(); }
	if (show_marker) {
		var latlng = new google.maps.LatLng(lat, lng);
		var marker = new google.maps.Marker(latlng);
		if (pinmarkmsg != "") {
			google.maps.Event.addListener(marker, "click", function() {
				map.openInfoWindowHtml(latlng, pinmarkmsg);
				});
		}
		map.addOverlay(marker);
	}
}});
}
//-->
</script>
