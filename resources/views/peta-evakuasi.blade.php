<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Peta Evakuasi — LARAS Control Center</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    
    <!-- Font sudah dimuat lewat partials.theme (Plus Jakarta Sans, Inter, JetBrains Mono, ikon) -->
    
    <!-- Leaflet & Leaflet Routing Machine CSS -->
    <!-- CSS Leaflet 1.9.4 + Routing Machine 3.2.12 ditanam langsung, supaya peta tetap rapi walau CDN gagal -->
    <style>
/* required styles */

.leaflet-pane,
.leaflet-tile,
.leaflet-marker-icon,
.leaflet-marker-shadow,
.leaflet-tile-container,
.leaflet-pane > svg,
.leaflet-pane > canvas,
.leaflet-zoom-box,
.leaflet-image-layer,
.leaflet-layer {
	position: absolute;
	left: 0;
	top: 0;
	}
.leaflet-container {
	overflow: hidden;
	}
.leaflet-tile,
.leaflet-marker-icon,
.leaflet-marker-shadow {
	-webkit-user-select: none;
	   -moz-user-select: none;
	        user-select: none;
	  -webkit-user-drag: none;
	}
/* Prevents IE11 from highlighting tiles in blue */
.leaflet-tile::selection {
	background: transparent;
}
/* Safari renders non-retina tile on retina better with this, but Chrome is worse */
.leaflet-safari .leaflet-tile {
	image-rendering: -webkit-optimize-contrast;
	}
/* hack that prevents hw layers "stretching" when loading new tiles */
.leaflet-safari .leaflet-tile-container {
	width: 1600px;
	height: 1600px;
	-webkit-transform-origin: 0 0;
	}
.leaflet-marker-icon,
.leaflet-marker-shadow {
	display: block;
	}
/* .leaflet-container svg: reset svg max-width decleration shipped in Joomla! (joomla.org) 3.x */
/* .leaflet-container img: map is broken in FF if you have max-width: 100% on tiles */
.leaflet-container .leaflet-overlay-pane svg {
	max-width: none !important;
	max-height: none !important;
	}
.leaflet-container .leaflet-marker-pane img,
.leaflet-container .leaflet-shadow-pane img,
.leaflet-container .leaflet-tile-pane img,
.leaflet-container img.leaflet-image-layer,
.leaflet-container .leaflet-tile {
	max-width: none !important;
	max-height: none !important;
	width: auto;
	padding: 0;
	}

.leaflet-container img.leaflet-tile {
	/* See: https://bugs.chromium.org/p/chromium/issues/detail?id=600120 */
	mix-blend-mode: plus-lighter;
}

.leaflet-container.leaflet-touch-zoom {
	-ms-touch-action: pan-x pan-y;
	touch-action: pan-x pan-y;
	}
.leaflet-container.leaflet-touch-drag {
	-ms-touch-action: pinch-zoom;
	/* Fallback for FF which doesn't support pinch-zoom */
	touch-action: none;
	touch-action: pinch-zoom;
}
.leaflet-container.leaflet-touch-drag.leaflet-touch-zoom {
	-ms-touch-action: none;
	touch-action: none;
}
.leaflet-container {
	-webkit-tap-highlight-color: transparent;
}
.leaflet-container a {
	-webkit-tap-highlight-color: rgba(51, 181, 229, 0.4);
}
.leaflet-tile {
	filter: inherit;
	visibility: hidden;
	}
.leaflet-tile-loaded {
	visibility: inherit;
	}
.leaflet-zoom-box {
	width: 0;
	height: 0;
	-moz-box-sizing: border-box;
	     box-sizing: border-box;
	z-index: 800;
	}
/* workaround for https://bugzilla.mozilla.org/show_bug.cgi?id=888319 */
.leaflet-overlay-pane svg {
	-moz-user-select: none;
	}

.leaflet-pane         { z-index: 400; }

.leaflet-tile-pane    { z-index: 200; }
.leaflet-overlay-pane { z-index: 400; }
.leaflet-shadow-pane  { z-index: 500; }
.leaflet-marker-pane  { z-index: 600; }
.leaflet-tooltip-pane   { z-index: 650; }
.leaflet-popup-pane   { z-index: 700; }

.leaflet-map-pane canvas { z-index: 100; }
.leaflet-map-pane svg    { z-index: 200; }

.leaflet-vml-shape {
	width: 1px;
	height: 1px;
	}
.lvml {
	behavior: url(#default#VML);
	display: inline-block;
	position: absolute;
	}


/* control positioning */

.leaflet-control {
	position: relative;
	z-index: 800;
	pointer-events: visiblePainted; /* IE 9-10 doesn't have auto */
	pointer-events: auto;
	}
.leaflet-top,
.leaflet-bottom {
	position: absolute;
	z-index: 1000;
	pointer-events: none;
	}
.leaflet-top {
	top: 0;
	}
.leaflet-right {
	right: 0;
	}
.leaflet-bottom {
	bottom: 0;
	}
.leaflet-left {
	left: 0;
	}
.leaflet-control {
	float: left;
	clear: both;
	}
.leaflet-right .leaflet-control {
	float: right;
	}
.leaflet-top .leaflet-control {
	margin-top: 10px;
	}
.leaflet-bottom .leaflet-control {
	margin-bottom: 10px;
	}
.leaflet-left .leaflet-control {
	margin-left: 10px;
	}
.leaflet-right .leaflet-control {
	margin-right: 10px;
	}


/* zoom and fade animations */

.leaflet-fade-anim .leaflet-popup {
	opacity: 0;
	-webkit-transition: opacity 0.2s linear;
	   -moz-transition: opacity 0.2s linear;
	        transition: opacity 0.2s linear;
	}
.leaflet-fade-anim .leaflet-map-pane .leaflet-popup {
	opacity: 1;
	}
.leaflet-zoom-animated {
	-webkit-transform-origin: 0 0;
	    -ms-transform-origin: 0 0;
	        transform-origin: 0 0;
	}
svg.leaflet-zoom-animated {
	will-change: transform;
}

.leaflet-zoom-anim .leaflet-zoom-animated {
	-webkit-transition: -webkit-transform 0.25s cubic-bezier(0,0,0.25,1);
	   -moz-transition:    -moz-transform 0.25s cubic-bezier(0,0,0.25,1);
	        transition:         transform 0.25s cubic-bezier(0,0,0.25,1);
	}
.leaflet-zoom-anim .leaflet-tile,
.leaflet-pan-anim .leaflet-tile {
	-webkit-transition: none;
	   -moz-transition: none;
	        transition: none;
	}

.leaflet-zoom-anim .leaflet-zoom-hide {
	visibility: hidden;
	}


/* cursors */

.leaflet-interactive {
	cursor: pointer;
	}
.leaflet-grab {
	cursor: -webkit-grab;
	cursor:    -moz-grab;
	cursor:         grab;
	}
.leaflet-crosshair,
.leaflet-crosshair .leaflet-interactive {
	cursor: crosshair;
	}
.leaflet-popup-pane,
.leaflet-control {
	cursor: auto;
	}
.leaflet-dragging .leaflet-grab,
.leaflet-dragging .leaflet-grab .leaflet-interactive,
.leaflet-dragging .leaflet-marker-draggable {
	cursor: move;
	cursor: -webkit-grabbing;
	cursor:    -moz-grabbing;
	cursor:         grabbing;
	}

/* marker & overlays interactivity */
.leaflet-marker-icon,
.leaflet-marker-shadow,
.leaflet-image-layer,
.leaflet-pane > svg path,
.leaflet-tile-container {
	pointer-events: none;
	}

.leaflet-marker-icon.leaflet-interactive,
.leaflet-image-layer.leaflet-interactive,
.leaflet-pane > svg path.leaflet-interactive,
svg.leaflet-image-layer.leaflet-interactive path {
	pointer-events: visiblePainted; /* IE 9-10 doesn't have auto */
	pointer-events: auto;
	}

/* visual tweaks */

.leaflet-container {
	background: #ddd;
	outline-offset: 1px;
	}
.leaflet-container a {
	color: #0078A8;
	}
.leaflet-zoom-box {
	border: 2px dotted #38f;
	background: rgba(255,255,255,0.5);
	}


/* general typography */
.leaflet-container {
	font-family: "Helvetica Neue", Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-size: 0.75rem;
	line-height: 1.5;
	}


/* general toolbar styles */

.leaflet-bar {
	box-shadow: 0 1px 5px rgba(0,0,0,0.65);
	border-radius: 4px;
	}
.leaflet-bar a {
	background-color: #fff;
	border-bottom: 1px solid #ccc;
	width: 26px;
	height: 26px;
	line-height: 26px;
	display: block;
	text-align: center;
	text-decoration: none;
	color: black;
	}
.leaflet-bar a,
.leaflet-control-layers-toggle {
	background-position: 50% 50%;
	background-repeat: no-repeat;
	display: block;
	}
.leaflet-bar a:hover,
.leaflet-bar a:focus {
	background-color: #f4f4f4;
	}
.leaflet-bar a:first-child {
	border-top-left-radius: 4px;
	border-top-right-radius: 4px;
	}
.leaflet-bar a:last-child {
	border-bottom-left-radius: 4px;
	border-bottom-right-radius: 4px;
	border-bottom: none;
	}
.leaflet-bar a.leaflet-disabled {
	cursor: default;
	background-color: #f4f4f4;
	color: #bbb;
	}

.leaflet-touch .leaflet-bar a {
	width: 30px;
	height: 30px;
	line-height: 30px;
	}
.leaflet-touch .leaflet-bar a:first-child {
	border-top-left-radius: 2px;
	border-top-right-radius: 2px;
	}
.leaflet-touch .leaflet-bar a:last-child {
	border-bottom-left-radius: 2px;
	border-bottom-right-radius: 2px;
	}

/* zoom control */

.leaflet-control-zoom-in,
.leaflet-control-zoom-out {
	font: bold 18px 'Lucida Console', Monaco, monospace;
	text-indent: 1px;
	}

.leaflet-touch .leaflet-control-zoom-in, .leaflet-touch .leaflet-control-zoom-out  {
	font-size: 22px;
	}


/* layers control */

.leaflet-control-layers {
	box-shadow: 0 1px 5px rgba(0,0,0,0.4);
	background: #fff;
	border-radius: 5px;
	}
.leaflet-control-layers-toggle {
	background-image: url(https://unpkg.com/leaflet@1.9.4/dist/images/layers.png);
	width: 36px;
	height: 36px;
	}
.leaflet-retina .leaflet-control-layers-toggle {
	background-image: url(https://unpkg.com/leaflet@1.9.4/dist/images/layers-2x.png);
	background-size: 26px 26px;
	}
.leaflet-touch .leaflet-control-layers-toggle {
	width: 44px;
	height: 44px;
	}
.leaflet-control-layers .leaflet-control-layers-list,
.leaflet-control-layers-expanded .leaflet-control-layers-toggle {
	display: none;
	}
.leaflet-control-layers-expanded .leaflet-control-layers-list {
	display: block;
	position: relative;
	}
.leaflet-control-layers-expanded {
	padding: 6px 10px 6px 6px;
	color: #333;
	background: #fff;
	}
.leaflet-control-layers-scrollbar {
	overflow-y: scroll;
	overflow-x: hidden;
	padding-right: 5px;
	}
.leaflet-control-layers-selector {
	margin-top: 2px;
	position: relative;
	top: 1px;
	}
.leaflet-control-layers label {
	display: block;
	font-size: 13px;
	font-size: 1.08333em;
	}
.leaflet-control-layers-separator {
	height: 0;
	border-top: 1px solid #ddd;
	margin: 5px -10px 5px -6px;
	}

/* Default icon URLs */
.leaflet-default-icon-path { /* used only in path-guessing heuristic, see L.Icon.Default */
	background-image: url(https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png);
	}


/* attribution and scale controls */

.leaflet-container .leaflet-control-attribution {
	background: #fff;
	background: rgba(255, 255, 255, 0.8);
	margin: 0;
	}
.leaflet-control-attribution,
.leaflet-control-scale-line {
	padding: 0 5px;
	color: #333;
	line-height: 1.4;
	}
.leaflet-control-attribution a {
	text-decoration: none;
	}
.leaflet-control-attribution a:hover,
.leaflet-control-attribution a:focus {
	text-decoration: underline;
	}
.leaflet-attribution-flag {
	display: inline !important;
	vertical-align: baseline !important;
	width: 1em;
	height: 0.6669em;
	}
.leaflet-left .leaflet-control-scale {
	margin-left: 5px;
	}
.leaflet-bottom .leaflet-control-scale {
	margin-bottom: 5px;
	}
.leaflet-control-scale-line {
	border: 2px solid #777;
	border-top: none;
	line-height: 1.1;
	padding: 2px 5px 1px;
	white-space: nowrap;
	-moz-box-sizing: border-box;
	     box-sizing: border-box;
	background: rgba(255, 255, 255, 0.8);
	text-shadow: 1px 1px #fff;
	}
.leaflet-control-scale-line:not(:first-child) {
	border-top: 2px solid #777;
	border-bottom: none;
	margin-top: -2px;
	}
.leaflet-control-scale-line:not(:first-child):not(:last-child) {
	border-bottom: 2px solid #777;
	}

.leaflet-touch .leaflet-control-attribution,
.leaflet-touch .leaflet-control-layers,
.leaflet-touch .leaflet-bar {
	box-shadow: none;
	}
.leaflet-touch .leaflet-control-layers,
.leaflet-touch .leaflet-bar {
	border: 2px solid rgba(0,0,0,0.2);
	background-clip: padding-box;
	}


/* popup */

.leaflet-popup {
	position: absolute;
	text-align: center;
	margin-bottom: 20px;
	}
.leaflet-popup-content-wrapper {
	padding: 1px;
	text-align: left;
	border-radius: 12px;
	}
.leaflet-popup-content {
	margin: 13px 24px 13px 20px;
	line-height: 1.3;
	font-size: 13px;
	font-size: 1.08333em;
	min-height: 1px;
	}
.leaflet-popup-content p {
	margin: 17px 0;
	margin: 1.3em 0;
	}
.leaflet-popup-tip-container {
	width: 40px;
	height: 20px;
	position: absolute;
	left: 50%;
	margin-top: -1px;
	margin-left: -20px;
	overflow: hidden;
	pointer-events: none;
	}
.leaflet-popup-tip {
	width: 17px;
	height: 17px;
	padding: 1px;

	margin: -10px auto 0;
	pointer-events: auto;

	-webkit-transform: rotate(45deg);
	   -moz-transform: rotate(45deg);
	    -ms-transform: rotate(45deg);
	        transform: rotate(45deg);
	}
.leaflet-popup-content-wrapper,
.leaflet-popup-tip {
	background: white;
	color: #333;
	box-shadow: 0 3px 14px rgba(0,0,0,0.4);
	}
.leaflet-container a.leaflet-popup-close-button {
	position: absolute;
	top: 0;
	right: 0;
	border: none;
	text-align: center;
	width: 24px;
	height: 24px;
	font: 16px/24px Tahoma, Verdana, sans-serif;
	color: #757575;
	text-decoration: none;
	background: transparent;
	}
.leaflet-container a.leaflet-popup-close-button:hover,
.leaflet-container a.leaflet-popup-close-button:focus {
	color: #585858;
	}
.leaflet-popup-scrolled {
	overflow: auto;
	}

.leaflet-oldie .leaflet-popup-content-wrapper {
	-ms-zoom: 1;
	}
.leaflet-oldie .leaflet-popup-tip {
	width: 24px;
	margin: 0 auto;

	-ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=0.70710678, M21=-0.70710678, M22=0.70710678)";
	filter: progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=0.70710678, M21=-0.70710678, M22=0.70710678);
	}

.leaflet-oldie .leaflet-control-zoom,
.leaflet-oldie .leaflet-control-layers,
.leaflet-oldie .leaflet-popup-content-wrapper,
.leaflet-oldie .leaflet-popup-tip {
	border: 1px solid #999;
	}


/* div icon */

.leaflet-div-icon {
	background: #fff;
	border: 1px solid #666;
	}


/* Tooltip */
/* Base styles for the element that has a tooltip */
.leaflet-tooltip {
	position: absolute;
	padding: 6px;
	background-color: #fff;
	border: 1px solid #fff;
	border-radius: 3px;
	color: #222;
	white-space: nowrap;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
	pointer-events: none;
	box-shadow: 0 1px 3px rgba(0,0,0,0.4);
	}
.leaflet-tooltip.leaflet-interactive {
	cursor: pointer;
	pointer-events: auto;
	}
.leaflet-tooltip-top:before,
.leaflet-tooltip-bottom:before,
.leaflet-tooltip-left:before,
.leaflet-tooltip-right:before {
	position: absolute;
	pointer-events: none;
	border: 6px solid transparent;
	background: transparent;
	content: "";
	}

/* Directions */

.leaflet-tooltip-bottom {
	margin-top: 6px;
}
.leaflet-tooltip-top {
	margin-top: -6px;
}
.leaflet-tooltip-bottom:before,
.leaflet-tooltip-top:before {
	left: 50%;
	margin-left: -6px;
	}
.leaflet-tooltip-top:before {
	bottom: 0;
	margin-bottom: -12px;
	border-top-color: #fff;
	}
.leaflet-tooltip-bottom:before {
	top: 0;
	margin-top: -12px;
	margin-left: -6px;
	border-bottom-color: #fff;
	}
.leaflet-tooltip-left {
	margin-left: -6px;
}
.leaflet-tooltip-right {
	margin-left: 6px;
}
.leaflet-tooltip-left:before,
.leaflet-tooltip-right:before {
	top: 50%;
	margin-top: -6px;
	}
.leaflet-tooltip-left:before {
	right: 0;
	margin-right: -12px;
	border-left-color: #fff;
	}
.leaflet-tooltip-right:before {
	left: 0;
	margin-left: -12px;
	border-right-color: #fff;
	}

/* Printing */

@media print {
	/* Prevent printers from removing background-images of controls. */
	.leaflet-control {
		-webkit-print-color-adjust: exact;
		print-color-adjust: exact;
		}
	}

.leaflet-routing-container, .leaflet-routing-error {
    width: 320px;
    background-color: white;
    padding-top: 4px;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.leaflet-control-container .leaflet-routing-container-hide {
    width: 32px;
    height: 32px;
}

.leaflet-routing-container h2 {
    font-size: 14px;
}

.leaflet-routing-container h3 {
    font-size: 12px;
    font-weight: normal;
}

.leaflet-routing-collapsible .leaflet-routing-geocoders {
    margin-top: 20px;
}

.leaflet-routing-alt, .leaflet-routing-geocoders, .leaflet-routing-error {
    padding: 6px;
    margin-top: 2px;
    margin-bottom: 6px;
    border-bottom: 1px solid #ccc;
    max-height: 320px;
    overflow-y: auto;
    transition: all 0.2s ease;
}

.leaflet-control-container .leaflet-routing-container-hide .leaflet-routing-alt, 
.leaflet-control-container .leaflet-routing-container-hide .leaflet-routing-geocoders {
    display: none;
}

.leaflet-bar .leaflet-routing-alt:last-child {
    border-bottom: none;
}

.leaflet-routing-alt-minimized {
    color: #888;
    max-height: 64px;
    overflow: hidden;
    cursor: pointer;
}

.leaflet-routing-alt table {
    border-collapse: collapse;
}

.leaflet-routing-alt tr:hover {
    background-color: #eee;
    cursor: pointer;
}

.leaflet-routing-alt::-webkit-scrollbar {
    width: 8px;
}

.leaflet-routing-alt::-webkit-scrollbar-track {
    border-radius: 2px;
    background-color: #eee;
}

.leaflet-routing-alt::-webkit-scrollbar-thumb {
    border-radius: 2px;
    background-color: #888;
}

.leaflet-routing-icon {
    background-image: url(https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet.routing.icons.png);
    -webkit-background-size: 240px 20px;
    background-size: 240px 20px;
    background-repeat: no-repeat;
    margin: 0;
    content: '';
    display: inline-block;
    vertical-align: top;
    width: 20px;
    height: 20px;
}

.leaflet-routing-icon-continue         { background-position: 0 0; }
.leaflet-routing-icon-sharp-right      { background-position: -20px 0; }
.leaflet-routing-icon-turn-right       { background-position: -40px 0; }
.leaflet-routing-icon-bear-right       { background-position: -60px 0; }
.leaflet-routing-icon-u-turn           { background-position: -80px 0; }
.leaflet-routing-icon-sharp-left       { background-position: -100px 0; }
.leaflet-routing-icon-turn-left        { background-position: -120px 0; }
.leaflet-routing-icon-bear-left        { background-position: -140px 0; }
.leaflet-routing-icon-depart           { background-position: -160px 0; }
.leaflet-routing-icon-enter-roundabout { background-position: -180px 0; }
.leaflet-routing-icon-arrive           { background-position: -200px 0; }
.leaflet-routing-icon-via              { background-position: -220px 0; }

.leaflet-routing-geocoders div {
    padding: 4px 0px 4px 0px;
}

.leaflet-routing-geocoders input {
    width: 303px;
    width: calc(100% - 4px);
    line-height: 1.67;
    border: 1px solid #ccc;
}

.leaflet-routing-geocoders button {
    font: bold 18px 'Lucida Console', Monaco, monospace;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: white;
    margin: 0;
    margin-right: 3px;
    float: right;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.leaflet-routing-add-waypoint:after {
    content: '+';
}

.leaflet-routing-reverse-waypoints:after {
    font-weight: normal;
    content: '\21c5';
}

.leaflet-routing-geocoders button:hover {
    background-color: #eee;
}

.leaflet-routing-geocoders input,.leaflet-routing-remove-waypoint,.leaflet-routing-geocoder {
    position: relative;
}

.leaflet-routing-geocoder-result {
    font: 12px/1.5 "Helvetica Neue", Arial, Helvetica, sans-serif;
    position: absolute;
    max-height: 0;
    overflow: hidden;
    transition: all 0.5s ease;
    z-index: 1000; /* Arbitrary, but try to be above "most" things. */
}

.leaflet-routing-geocoder-result table {
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 0 0 4px 4px;
    background-color: white;
    cursor: pointer;
}

.leaflet-routing-geocoder-result-open {
    max-height: 800px;
}

.leaflet-routing-geocoder-selected, .leaflet-routing-geocoder-result tr:hover {
    background-color: #eee;
}

.leaflet-routing-geocoder-no-results {
    font-style: italic;
    color: #888;
}

.leaflet-routing-remove-waypoint {
    background-color: transparent;
    display: inline-block;
    vertical-align: middle;
    cursor: pointer;
}

.leaflet-routing-remove-waypoint:after {
    position: absolute;
    display: block;
    width: 15px;
    height: 1px;
    z-index: 1;
    right: 1px;
    top: 4px;
    bottom: 0;
    margin: auto;
    padding: 2px;
    font-size: 18px;
    font-weight: bold;
    content: "\00d7";
    text-align: center;
    cursor: pointer;
    color: #ccc;
    background: white;
    padding-bottom: 16px;
    margin-top: -16px;
    padding-right: 4px;
    line-height: 1;
}

.leaflet-routing-remove-waypoint:hover {
    color: black;
}

.leaflet-routing-instruction-distance {
    width: 48px;
}

.leaflet-routing-collapse-btn {
    position: absolute;
    top: 0;
    right: 6px;
    font-size: 24px;
    color: #ccc;
    font-weight: bold;
}

.leaflet-routing-collapse-btn:after {
    content: '\00d7';
}

.leaflet-routing-container-hide .leaflet-routing-collapse-btn {
    position: relative;
    left: 4px;
    top: 4px;
    display: block;
    width: 26px;
    height: 23px;
    background-image: url(https://unpkg.com/leaflet-routing-machine@3.2.12/dist/routing-icon.png);
}

.leaflet-routing-container-hide .leaflet-routing-collapse-btn:after {
    content: none;
}

.leaflet-top .leaflet-routing-container.leaflet-routing-container-hide {
    margin-top: 10px !important;
}
.leaflet-right .leaflet-routing-container.leaflet-routing-container-hide {
    margin-right: 10px !important;
}
.leaflet-bottom .leaflet-routing-container.leaflet-routing-container-hide {
    margin-bottom: 10px !important;
}
.leaflet-left .leaflet-routing-container.leaflet-routing-container-hide {
    margin-left: 10px !important;
}

@media only screen and (max-width: 640px) {
    .leaflet-routing-container {
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
        height: 100%;
    }
}

    </style>

    <style>
        #map { height: 500px; width: 100%; border-radius: .75rem; z-index: 0; }
        .leaflet-popup-content-wrapper { border-radius: 12px; }
        .leaflet-routing-container { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-air-50 text-deep-900 min-h-screen pb-12">

    @include('partials.navbar', ['active' => 'peta'])

    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 space-y-6">

        <!-- Title Section -->
        <div>
            <span class="section-tag">Navigasi Penyelamatan</span>
            <h2 class="text-[1.75rem] sm:text-[2rem] font-bold text-deep-900 mt-2">Peta Evakuasi Kawasan Pacet</h2>
            <p class="text-deep-500 mt-1 max-w-2xl text-[14px] leading-relaxed">
                Visualisasi titik node LARAS di perbukitan Pacet beserta status risiko terkini. Rute evakuasi memandu warga mengikuti kontur jalan dari titik bahaya/lereng menuju Posko Aman terdekat.
            </p>
        </div>

        <!-- Legend Badges -->
        <div class="flex flex-wrap gap-3">
            <div class="flex items-center gap-2 bg-white border border-air-300 px-3 py-1.5 rounded-full text-xs font-semibold text-deep-700">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Aman (Posko)
            </div>
            <div class="flex items-center gap-2 bg-white border border-air-300 px-3 py-1.5 rounded-full text-xs font-semibold text-deep-700">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span> Waspada
            </div>
            <div class="flex items-center gap-2 bg-white border border-air-300 px-3 py-1.5 rounded-full text-xs font-semibold text-deep-700">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span> Bahaya
            </div>
            <div class="flex items-center gap-2 bg-white border border-air-300 px-3 py-1.5 rounded-full text-xs font-semibold text-deep-700">
                <span class="w-4 h-0.5 bg-rose-500 border-t-2 border-dashed border-rose-500"></span> Rute Evakuasi Jalan
            </div>
        </div>

        <!-- Map Container -->
        <div class="card p-2"><div id="map"></div></div>

        @php $canManage = auth()->user()?->can('kelola-data') ?? false; @endphp

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl">
            <p class="font-bold mb-1">Data node belum tersimpan:</p>
            <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <!-- Detail Node Cards -->
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-deep-900 text-[18px]">Node Sensor</h3>
                <p class="text-[13px] text-deep-500">Status risiko, baterai, dan lokasi tiap node</p>
            </div>
            @if($canManage)
            <button type="button" onclick="openNodeForm(null)" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-teal-500 hover:bg-teal-600 text-white text-[13px] font-semibold font-heading transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Node
            </button>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($nodes as $node)
            @php
                $statusColor = match(strtoupper($node->status)) {
                    'BAHAYA' => ['bg-rose-50', 'border-rose-200', 'text-rose-700', 'bg-rose-500'],
                    'WASPADA' => ['bg-amber-50', 'border-amber-200', 'text-amber-700', 'bg-amber-500'],
                    'AMAN' => ['bg-emerald-50', 'border-emerald-200', 'text-emerald-700', 'bg-emerald-500'],
                    default => ['bg-slate-100', 'border-air-300', 'text-slate-600', 'bg-slate-400'],
                };
                $statusLabel = match (strtoupper($node->status)) { 'UNKNOWN' => 'BELUM ADA DATA', 'PENDING' => 'MENUNGGU ANALISIS', default => strtoupper($node->status) };
                $bat = $node->battery;
                $batColor = $bat === null ? 'bg-slate-300' : ($bat >= 50 ? 'bg-emerald-500' : ($bat >= 20 ? 'bg-amber-500' : 'bg-rose-500'));
                $batText  = $bat === null ? 'text-slate-400' : ($bat >= 50 ? 'text-emerald-700' : ($bat >= 20 ? 'text-amber-700' : 'text-rose-700'));
            @endphp
            <div class="bg-white rounded-xl border {{ $statusColor[1] }} shadow-card p-5 hover:shadow-lift hover:-translate-y-0.5 transition flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-deep-900 text-[16px] font-heading">{{ $node->node_name }}</h4>
                    <span class="flex items-center gap-1.5 text-[10px] font-bold font-mono tracking-wide {{ $statusColor[2] }} {{ $statusColor[0] }} px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusColor[3] }}"></span>
                        {{ $statusLabel }}
                    </span>
                </div>
                <p class="text-[11px] text-deep-400 font-mono">{{ $node->desa ?: '-' }} · {{ number_format($node->lat, 5) }}, {{ number_format($node->lng, 5) }}</p>
                @if($node->notes)
                <p class="text-[13px] text-deep-600 mt-2 leading-relaxed">{{ $node->notes }}</p>
                @endif

                <!-- Power monitoring -->
                <div class="mt-auto pt-3 border-t border-air-200 flex items-center gap-3">
                    <div class="flex items-center gap-1" title="Baterai">
                        <div class="relative w-9 h-[18px] rounded-[4px] border-2 border-slate-400 p-[2px]">
                            <div class="h-full rounded-[2px] {{ $batColor }}" style="width: {{ $bat === null ? 0 : max(4, min(100, $bat)) }}%"></div>
                        </div>
                        <div class="w-[3px] h-2 rounded-r-sm bg-slate-400"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        @if($bat !== null)
                            <p class="text-sm font-bold font-mono {{ $batText }} leading-tight">{{ $bat }}%@if($node->voltage) <span class="text-[11px] font-medium text-slate-400">· {{ number_format($node->voltage, 2) }} V</span>@endif</p>
                            <p class="text-[10px] text-slate-400">Update {{ $node->battery_at }}@if($bat < 20) · <span class="font-bold text-rose-600">segera isi daya</span>@endif</p>
                        @else
                            <p class="text-xs font-semibold text-slate-400 leading-tight">Belum ada data baterai</p>
                            <p class="text-[10px] text-slate-400">Menunggu kiriman dari node</p>
                        @endif
                    </div>
                    @if($canManage)
                    <button type="button" onclick='openNodeForm(@json($node))' class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-air-300 text-[11px] font-bold text-teal-700 hover:bg-air-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-6 8-12a8 8 0 1 0-16 0c0 6 8 12 8 12z"/><circle cx="12" cy="10" r="3"/></svg>
                        Edit lokasi
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-xl border border-dashed border-air-300 p-8 text-center text-sm text-deep-400">
                Belum ada data node tersimpan.
            </div>
            @endforelse
        </div>

        @if($canManage)
        <!-- Modal form koordinat node (hanya Admin & Kader) -->
        <div id="nodeModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-deep-900/40 px-4" role="dialog" aria-modal="true" aria-labelledby="nodeModalTitle">
            <form id="nodeForm" method="POST" action="{{ route('nodes.store') }}" class="w-full max-w-md bg-white rounded-xl border border-air-300 shadow-float p-6 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="nodeMethod" value="POST">
                <div class="flex items-center justify-between">
                    <h3 id="nodeModalTitle" class="font-bold text-deep-900 text-[17px]">Tambah Node</h3>
                    <button type="button" onclick="closeNodeForm()" aria-label="Tutup" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="nf_code" class="block text-xs font-bold text-slate-500 uppercase mb-1">Kode Node</label>
                        <input id="nf_code" name="node_code" required maxlength="50" placeholder="Node-07" class="w-full px-3 py-2 border border-air-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="nf_desa" class="block text-xs font-bold text-slate-500 uppercase mb-1">Desa</label>
                        <input id="nf_desa" name="desa" maxlength="100" placeholder="Padusan" class="w-full px-3 py-2 border border-air-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="nf_lat" class="block text-xs font-bold text-slate-500 uppercase mb-1">Latitude</label>
                        <input id="nf_lat" name="latitude" type="number" step="0.000001" min="-90" max="90" required placeholder="-7.691000" class="w-full px-3 py-2 border border-air-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="nf_lng" class="block text-xs font-bold text-slate-500 uppercase mb-1">Longitude</label>
                        <input id="nf_lng" name="longitude" type="number" step="0.000001" min="-180" max="180" required placeholder="112.541000" class="w-full px-3 py-2 border border-air-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" id="btnGps" onclick="useMyGps()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-air-100 hover:bg-air-200 text-teal-700 text-xs font-bold transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
                        Ambil dari GPS HP
                    </button>
                    <button type="button" onclick="pickOnMap()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 6v16l7-4 8 4 7-4V2l-7 4-8-4-7 4z"/></svg>
                        Pilih di peta
                    </button>
                </div>
                <p id="gpsInfo" class="text-[11px] text-slate-400 -mt-2">Berdirilah di dekat node saat mengambil GPS agar koordinatnya akurat.</p>

                <div>
                    <label for="nf_notes" class="block text-xs font-bold text-slate-500 uppercase mb-1">Keterangan</label>
                    <textarea id="nf_notes" name="notes" rows="2" maxlength="500" placeholder="Kondisi sekitar node..." class="w-full px-3 py-2 border border-air-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-1">
                    <button type="button" onclick="closeNodeForm()" class="px-4 py-2 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-teal-500 hover:bg-teal-600 text-white text-xs font-bold">Simpan</button>
                </div>
            </form>
        </div>

        <!-- Petunjuk mode pilih di peta -->
        <div id="pickHint" class="fixed top-20 left-1/2 -translate-x-1/2 z-[55] hidden items-center gap-3 bg-deep-900 text-white text-xs font-semibold px-4 py-2.5 rounded-full shadow-lg">
            Klik lokasi node di peta
            <button type="button" onclick="cancelPick()" class="text-cream-200 hover:text-cream-100 font-bold">Batal</button>
        </div>
        @endif

        <!-- Section Sistem Check-in Warga Posko -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Form Check-in Warga (HANYA ADMIN & KADER) -->
            @can('kelola-data')
            <div class="card p-5 lg:col-span-1">
                <div class="flex items-center gap-2 mb-4 border-b border-air-200 pb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="font-bold text-deep-900 text-[16px]">Form Check-In Warga di Posko</h3>
                </div>

                <form id="formCheckin" class="space-y-4">

                    <!-- Pilihan Posko Tujuan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Pilih Posko Evakuasi</label>
                        <select id="selectPosko" required class="w-full px-3 py-2 border border-air-300 rounded-lg text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50">
                            @foreach($daftarPosko as $p)
                                <option value="{{ $p['id'] }}" data-lokasi="{{ $p['lokasi'] }}">
                                     {{ $p['nama'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Keterangan Lokasi Posko (Auto Fill) -->
                    <div class="info-note p-3 text-xs">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Lokasi/Alamat Posko:</span>
                        <p id="teksLokasiPosko" class="font-medium text-slate-700 mt-0.5">{{ $daftarPosko[0]['lokasi'] }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap Warga</label>
                        <input type="text" id="nama_lengkap" required placeholder="Contoh: Budi Santoso"
                            class="w-full px-3 py-2 border border-air-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Asal / RT / RW</label>
                        <input type="text" id="asal" required placeholder="Contoh: Dusun Padusan RT 02 RW 01"
                            class="w-full px-3 py-2 border border-air-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Usia (Tahun)</label>
                        <input type="number" id="usia" min="0" max="120" required placeholder="Contoh: 38"
                            class="w-full px-3 py-2 border border-air-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Kondisi Kesehatan</label>
                        <select id="kondisi" required class="w-full px-3 py-2 border border-air-300 rounded-lg text-xs focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                            <option value="Sehat">Sehat</option>
                            <option value="Luka Ringan">Luka Ringan</option>
                            <option value="Luka Berat">Luka Berat</option>
                            <option value="Lansia / Rentan">Lansia / Rentan</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-white font-semibold font-heading py-3 rounded-lg text-[13px] transition shadow-sm">
                        + Simpan Check-In Warga
                    </button>
                </form>
            </div>
            @else
            <!-- Info untuk Warga / Guest (mode lihat saja) -->
            <div class="bg-cream-50 p-5 rounded-xl border border-cream-200 shadow-card lg:col-span-1 text-[13px] text-cream-900 space-y-3 h-fit">
                <div class="flex items-center gap-2 border-b border-amber-200 pb-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <h3 class="font-bold text-amber-900 text-sm">Check-In Warga di Posko</h3>
                </div>
                <p>⚠️ Anda mengakses halaman ini sebagai <strong>Warga (Guest)</strong>. Pendataan check-in warga di posko hanya dilakukan oleh <strong>Kader &amp; Admin</strong>.</p>
                <p>Silakan datang ke posko evakuasi terdekat dan laporkan diri Anda kepada petugas.</p>
                @guest
                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-teal-500 hover:bg-teal-600 text-white font-semibold">Masuk Petugas →</a>
                @endguest
            </div>
            @endcan

            <!-- Tabel Data Check-in Warga + FILTER POSKO -->
            <div class="card p-5 lg:col-span-2">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-air-200 pb-3 mb-4 gap-3">
                    <div>
                        <h3 class="font-bold text-deep-900 text-[16px]">
                            Rekap Warga Check-In
                        </h3>
                        <p class="text-[11px] text-slate-400">Filter data warga berdasarkan posko keberadaan saat ini</p>
                    </div>

                    <!-- Filter Dropdown Posko -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500 shrink-0">Filter Posko:</label>
                        <select id="filterPosko" class="px-3 py-1.5 border border-air-300 rounded-xl text-xs font-bold text-slate-700 bg-slate-50 focus:outline-none">
                            <option value="SEMUA">Semua Posko</option>
                            @foreach($daftarPosko as $p)
                                <option value="{{ $p['id'] }}"> {{ $p['id'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <span id="labelTotal" class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200 font-mono">
                        Total: {{ count($checkins) }} Warga
                    </span>
                </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-600">
                    <thead class="text-[11px] text-white uppercase bg-deep-900 font-mono">
                        <tr>
                            <th class="px-2 py-2.5 w-8">No</th>
                            <th class="px-3 py-2.5">Nama Warga</th>
                            <th class="px-3 py-2.5">Posko Check-in & Lokasi</th>
                            <th class="px-3 py-2.5 min-w-[120px]">Asal</th>
                            <th class="px-2 py-2.5 w-14">Usia</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Kondisi</th>
                            <th class="px-3 py-2.5 whitespace-nowrap">Waktu</th>
                            @can('kelola-data')
                            <th class="px-3 py-2.5 whitespace-nowrap">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody id="bodyTabel">
                        @forelse($checkins as $key => $warga)
                        <tr class="border-b border-air-200 hover:bg-air-50">
                            <td class="px-2 py-2.5 font-medium font-mono text-center align-top">{{ $key + 1 }}</td>
                            <td class="px-3 py-2.5 font-bold text-slate-800 align-top">{{ $warga->nama_lengkap }}</td>
                            <td class="px-3 py-2.5 align-top">
                                <span class="font-bold text-teal-700 block">{{ $warga->posko_id }}</span>
                                <span class="text-[10px] text-slate-400 block leading-tight">{{ $warga->lokasi_posko }}</span>
                            </td>
                            <!-- Kolom Asal: Boleh melipat ke baris bawah secara rapi (Tanpa titik-titik) -->
                            <td class="px-3 py-2.5 align-top leading-relaxed text-slate-700 min-w-[120px]">{{ $warga->asal }}</td>
                            <td class="px-2 py-2.5 font-mono whitespace-nowrap align-top">{{ $warga->usia }} Thn</td>
                            <!-- Kolom Kondisi: Dikunci agar tetap 1 baris utuh -->
                            <td class="px-3 py-2.5 whitespace-nowrap align-top">
                                @if($warga->kondisi == 'Sehat')
                                    <span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">🟢 Sehat</span>
                                @elseif($warga->kondisi == 'Luka Ringan')
                                    <span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Ringan</span>
                                @elseif($warga->kondisi == 'Luka Berat')
                                    <span class="inline-block bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Berat</span>
                                @else
                                    <span class="inline-block bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Lansia / Rentan</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-400 font-mono text-[11px] whitespace-nowrap align-top">{{ optional($warga->created_at)->format('H:i') }} WIB</td>
                            @can('kelola-data')
                            <td class="px-3 py-2.5 align-top">
                                <button type="button" onclick="hapusCheckin({{ $warga->id }})" class="text-rose-500 hover:text-rose-700 text-[11px] font-bold hover:underline">Hapus</button>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $canManage ? 8 : 7 }}" class="text-center py-6 text-slate-400 font-mono">Belum ada warga terdaftar di posko.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Leaflet JS & Leaflet Routing Machine -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

    <script>
        const nodes = @json($nodes);
        const CAN_MANAGE = @json($canManage);
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Cegah XSS: data warga dimasukkan ke innerHTML, jadi harus di-escape
        const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

        // Baca respons JSON + tangani error auth / validasi dengan pesan yang jelas
        async function handleJson(res) {
            const body = await res.json().catch(() => ({}));
            if (res.status === 401) throw new Error(body.message || 'Sesi Anda berakhir. Silakan login kembali sebagai Kader / Admin.');
            if (res.status === 403) throw new Error(body.message || 'Akses ditolak. Hanya Kader / Admin yang boleh melakukan aksi ini.');
            if (res.status === 419) throw new Error('Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
            if (res.status === 422) throw new Error(Object.values(body.errors || {}).flat().join('\n') || body.message);
            if (!res.ok) throw new Error(body.message || 'Terjadi kesalahan server.');
            return body;
        }

        const map = L.map('map').setView([-7.66744, 112.53658], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18,
        }).addTo(map);

        const statusColor = (status) => {
            status = (status || '').toUpperCase();
            if (status === 'BAHAYA') return '#e11d48';
            if (status === 'WASPADA') return '#f59e0b';
            if (status === 'AMAN') return '#10b981';
            return '#94a3b8';
        };

        function makeIcon(color) {
            return L.divIcon({
                className: '',
                html: `<div style="background:${color}; width:18px; height:18px; border-radius:50%; border:3px solid white; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });
        }

        // Plot Marker Node di Peta
        nodes.forEach(node => {
            const lat = parseFloat(node.lat);
            const lng = parseFloat(node.lng);
            const color = statusColor(node.status);

            const marker = L.marker([lat, lng], { icon: makeIcon(color) }).addTo(map);
            marker.bindPopup(`
                <div style="font-family: Inter, sans-serif; min-width: 160px;">
                    <p style="font-weight:700; font-size:13px; margin-bottom:2px;">${esc(node.node_name)}</p>
                    <p style="font-size:11px; color:#64748b; margin-bottom:4px;">${esc(node.desa || '')}</p>
                    <span style="font-size:10px; font-weight:700; color:${color}; text-transform:uppercase;">${node.status === 'UNKNOWN' ? 'Belum ada data' : node.status}</span>
                    <p style="font-size:11px; color:#475569; margin-top:4px;">Baterai: <b>${node.battery === null ? '-' : node.battery + '%'}</b></p>
                </div>
            `);
        });

        // ===== Form koordinat node (hanya Admin & Kader) =====
        let pickMarker = null, picking = false;
        const nodeModal = document.getElementById('nodeModal');

        function openNodeForm(node) {
            if (!CAN_MANAGE) return;
            const f = document.getElementById('nodeForm');
            document.getElementById('nodeModalTitle').textContent = node ? `Edit Lokasi ${node.node_code}` : 'Tambah Node';
            f.action = node ? `{{ url('/nodes') }}/${node.id}` : `{{ route('nodes.store') }}`;
            document.getElementById('nodeMethod').value = node ? 'PUT' : 'POST';
            document.getElementById('nf_code').value  = node ? node.node_code : '';
            document.getElementById('nf_desa').value  = node ? (node.desa || '') : '';
            document.getElementById('nf_lat').value   = node ? Number(node.lat).toFixed(6) : '';
            document.getElementById('nf_lng').value   = node ? Number(node.lng).toFixed(6) : '';
            document.getElementById('nf_notes').value = node ? (node.notes || '') : '';
            document.getElementById('gpsInfo').textContent = 'Berdirilah di dekat node saat mengambil GPS agar koordinatnya akurat.';
            nodeModal.classList.remove('hidden'); nodeModal.classList.add('flex');
            showPickMarker();
        }

        function closeNodeForm() {
            nodeModal.classList.add('hidden'); nodeModal.classList.remove('flex');
            if (pickMarker) { map.removeLayer(pickMarker); pickMarker = null; }
        }

        function setCoords(lat, lng) {
            document.getElementById('nf_lat').value = Number(lat).toFixed(6);
            document.getElementById('nf_lng').value = Number(lng).toFixed(6);
            showPickMarker();
        }

        function showPickMarker() {
            const lat = parseFloat(document.getElementById('nf_lat').value);
            const lng = parseFloat(document.getElementById('nf_lng').value);
            if (isNaN(lat) || isNaN(lng)) return;
            if (!pickMarker) pickMarker = L.marker([lat, lng], { icon: makeIcon('#FAB44B') }).addTo(map);
            pickMarker.setLatLng([lat, lng]);
        }

        function useMyGps() {
            const info = document.getElementById('gpsInfo');
            if (!navigator.geolocation) { info.textContent = 'Browser ini tidak mendukung GPS. Isi koordinat manual atau pilih di peta.'; return; }
            info.textContent = 'Mengambil lokasi GPS...';
            navigator.geolocation.getCurrentPosition(
                pos => {
                    setCoords(pos.coords.latitude, pos.coords.longitude);
                    info.textContent = `Lokasi didapat (akurasi ±${Math.round(pos.coords.accuracy)} m). Periksa lalu klik Simpan.`;
                },
                err => {
                    info.textContent = err.code === 1
                        ? 'Izin lokasi ditolak. Izinkan akses lokasi di browser, atau pilih di peta.'
                        : 'Gagal mengambil GPS. Pastikan GPS HP aktif, atau pilih di peta.';
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        function pickOnMap() {
            picking = true;
            nodeModal.classList.add('hidden'); nodeModal.classList.remove('flex');
            const hint = document.getElementById('pickHint');
            hint.classList.remove('hidden'); hint.classList.add('flex');
            document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.getElementById('map').style.cursor = 'crosshair';
        }

        function endPick() {
            picking = false;
            const hint = document.getElementById('pickHint');
            hint.classList.add('hidden'); hint.classList.remove('flex');
            document.getElementById('map').style.cursor = '';
            nodeModal.classList.remove('hidden'); nodeModal.classList.add('flex');
        }
        function cancelPick() { endPick(); }

        if (CAN_MANAGE) {
            map.on('click', e => {
                if (!picking) return;
                setCoords(e.latlng.lat, e.latlng.lng);
                endPick();
            });
            ['nf_lat', 'nf_lng'].forEach(id => document.getElementById(id).addEventListener('input', showPickMarker));
            nodeModal.addEventListener('click', e => { if (e.target === nodeModal) closeNodeForm(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape' && !nodeModal.classList.contains('hidden')) closeNodeForm(); });
            @if($errors->any() && old('node_code'))
                openNodeForm(null);
                document.getElementById('nf_code').value = @json(old('node_code'));
                document.getElementById('nf_desa').value = @json(old('desa'));
                document.getElementById('nf_lat').value = @json(old('latitude'));
                document.getElementById('nf_lng').value = @json(old('longitude'));
                document.getElementById('nf_notes').value = @json(old('notes'));
            @endif
        }

        // Routing Realistis Mengikuti Kontur Jalan
        L.Routing.control({
            waypoints: [
                L.latLng(-7.69100, 112.54100), // Node-01 Padusan (Bahaya)
                L.latLng(-7.67500, 112.53500), // Node-02 Sajen
                L.latLng(-7.66200, 112.54000), // Node-03 Claket
                L.latLng(-7.65200, 112.52900)  // Node-04 Posko Cepokolimo (Aman)
            ],
            lineOptions: {
                styles: [{ color: '#e11d48', opacity: 0.85, weight: 5, dashArray: '8, 8' }]
            },
            createMarker: function() { return null; },
            addWaypoints: false,
            draggableWaypoints: false
        }).addTo(map);

        L.Routing.control({
            waypoints: [
                L.latLng(-7.66200, 112.54000), // Node-03 Claket
                L.latLng(-7.64400, 112.54700)  // Node-05 Kembangbelor (Aman)
            ],
            lineOptions: {
                styles: [{ color: '#10b981', opacity: 0.85, weight: 5 }]
            },
            createMarker: function() { return null; },
            addWaypoints: false,
            draggableWaypoints: false
        }).addTo(map);

        if (nodes.length > 0) {
            const bounds = L.latLngBounds(nodes.map(n => [parseFloat(n.lat), parseFloat(n.lng)]));
            map.fitBounds(bounds, { padding: [40, 40] });
        }

        // ===== Fitur check-in (hanya dirender untuk Admin & Kader) =====
        const selectPosko = document.getElementById('selectPosko');
        const teksLokasiPosko = document.getElementById('teksLokasiPosko');
        const formCheckin = document.getElementById('formCheckin');

        if (CAN_MANAGE && formCheckin) {
            selectPosko.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                teksLokasiPosko.innerText = selectedOption.getAttribute('data-lokasi');
            });

            formCheckin.addEventListener('submit', function(e) {
                e.preventDefault();

                const dataPayload = {
                    posko_id: selectPosko.value,
                    nama_lengkap: document.getElementById('nama_lengkap').value,
                    asal: document.getElementById('asal').value,
                    usia: document.getElementById('usia').value,
                    kondisi: document.getElementById('kondisi').value
                };

                fetch('{{ route("peta-evakuasi.checkin") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(dataPayload)
                })
                .then(handleJson)
                .then(res => {
                    alert(res.message);
                    formCheckin.reset();
                    teksLokasiPosko.innerText = selectPosko.options[0].getAttribute('data-lokasi');
                    updateCheckinTable(filterPosko.value);
                })
                .catch(err => alert(err.message || 'Terjadi kesalahan koneksi server.'));
            });
        }

        function hapusCheckin(id) {
            if (!CAN_MANAGE || !confirm('Hapus data check-in warga ini?')) return;

            fetch(`{{ url('/peta-evakuasi/checkin') }}/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
            })
            .then(handleJson)
            .then(() => updateCheckinTable(filterPosko.value))
            .catch(err => alert(err.message || 'Terjadi kesalahan koneksi server.'));
        }

        // Handling Filter Posko
        const filterPosko = document.getElementById('filterPosko');
        filterPosko.addEventListener('change', function() {
            updateCheckinTable(this.value);
        });

        // Update Tabel Realtime dengan Support Filter
        function updateCheckinTable(selectedFilter = 'SEMUA') {
            fetch(`{{ route("peta-evakuasi.checkins-list") }}?posko_id=${encodeURIComponent(selectedFilter)}`, { headers: { 'Accept': 'application/json' } })
            .then(handleJson)
            .then(data => {
                document.getElementById('labelTotal').innerText = `Total: ${data.length} Warga`;
                const tbody = document.getElementById('bodyTabel');

                if(data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="${CAN_MANAGE ? 8 : 7}" class="text-center py-6 text-slate-400 font-mono">Belum ada warga terdaftar di posko ini.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.map((item, idx) => {
                    let badge = '';
                    if(item.kondisi === 'Sehat') badge = '<span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Sehat</span>';
                    else if(item.kondisi === 'Luka Ringan') badge = '<span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Ringan</span>';
                    else if(item.kondisi === 'Luka Berat') badge = '<span class="inline-block bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold">Luka Berat</span>';
                    else badge = '<span class="inline-block bg-sky-50 text-sky-700 border border-sky-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold"> Lansia / Rentan</span>';

                    const time = new Date(item.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

                    return `
                        <tr class="border-b border-air-200 hover:bg-air-50">
                            <td class="px-2 py-2.5 font-medium font-mono text-center align-top">${idx + 1}</td>
                            <td class="px-3 py-2.5 font-bold text-slate-800 align-top">${esc(item.nama_lengkap)}</td>
                            <td class="px-3 py-2.5 align-top">
                                <span class="font-bold text-teal-700 block">${esc(item.posko_id)}</span>
                                <span class="text-[10px] text-slate-400 block leading-tight">${esc(item.lokasi_posko)}</span>
                            </td>
                            <td class="px-3 py-2.5 align-top leading-relaxed text-slate-700 min-w-[120px]">${esc(item.asal)}</td>
                            <td class="px-2 py-2.5 font-mono whitespace-nowrap align-top">${esc(item.usia)} Thn</td>
                            <td class="px-3 py-2.5 whitespace-nowrap align-top">${badge}</td>
                            <td class="px-3 py-2.5 text-slate-400 font-mono text-[11px] whitespace-nowrap align-top">${time} WIB</td>
                            ${CAN_MANAGE ? `<td class="px-3 py-2.5 align-top"><button type="button" onclick="hapusCheckin(${Number(item.id)})" class="text-rose-500 hover:text-rose-700 text-[11px] font-bold hover:underline">Hapus</button></td>` : ''}
                        </tr>
                    `;
                }).join('');
            })
            .catch(err => console.error(err));
        }
    </script>
</body>
</html>