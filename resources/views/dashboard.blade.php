<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LARAS Control Center — Landslide Alert and Rescue Adaptive System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.theme')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- CSS Leaflet ditanam langsung, supaya peta mini tetap rapi walau CDN gagal -->
    <style id="leaflet-css">
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>

    <style>
        .leaflet-popup-content-wrapper { border-radius: 12px; }
        .leaflet-container { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        @keyframes pulse-ring {
            0% { transform: scale(0.93); opacity: 1; }
            80%, 100% { transform: scale(1.15); opacity: 0; }
        }
        .pulse-ring {
            animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        }
        @keyframes fade-slide-up {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fade-slide-up 0.6s ease forwards; }
        .animate-fade-up-delay-1 { animation: fade-slide-up 0.6s ease 0.15s forwards; opacity: 0; }
        .animate-fade-up-delay-2 { animation: fade-slide-up 0.6s ease 0.3s forwards; opacity: 0; }
        .animate-fade-up-delay-3 { animation: fade-slide-up 0.6s ease 0.45s forwards; opacity: 0; }

        #landing-page { transition: opacity 0.4s ease, transform 0.4s ease; }
        #dashboard-page { transition: opacity 0.4s ease, transform 0.4s ease; display: none; }

        .status-aman { background: #f0fdf4; border-color: #22c55e; }
        .status-waspada { background: #fff9ed; border-color: #FAB44B; }
        .status-bahaya { background: #fff1f2; border-color: #f43f5e; }
        .status-unknown { background: #f8fafc; border-color: #94a3b8; }

        .bar-aman { width: var(--bar-w); background: #22c55e; }
        .bar-waspada { width: var(--bar-w); background: #FAB44B; }
        .bar-bahaya { width: var(--bar-w); background: #f43f5e; }

        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); }

        details.edu-accordion summary { cursor: pointer; list-style: none; }
        details.edu-accordion summary::-webkit-details-marker { display: none; }
        details.edu-accordion[open] summary .chev { transform: rotate(180deg); }

        @keyframes float-hero {
            0%   { transform: translateY(20px); opacity: 0.7; }
            50%  { transform: translateY(-15px); opacity: 1; }
            100% { transform: translateY(20px); opacity: 0.7; }
        }
        
    </style>

    <script>
        const phpLogs    = @json($logs);
        const phpCurrent = @json($current ?? null);
        const phpWeather = @json($weatherInfo ?? 'N/A');
        // jam server (zona waktu aplikasi) untuk menghitung "diperbarui X menit lalu" dengan benar
        const SERVER_NOW_MS = new Date(@json(now()->format('Y-m-d\TH:i:s'))).getTime();
        const CLOCK_SKEW_MS = isNaN(SERVER_NOW_MS) ? 0 : Date.now() - SERVER_NOW_MS;
    </script>
</head>
<body class="bg-white antialiased text-deep-900 overflow-x-hidden" data-default-view="{{ auth()->check() ? 'teknis' : 'warga' }}">

    @include('partials.navbar', ['active' => 'home'])


<div id="landing-page">
@php
    // ===== Data ringkas untuk Beranda (semua dari database, tidak ada angka karangan) =====
    $nodes   = collect($nodes ?? [])->map(function ($n) {
        $n = (object) $n;
        foreach (['status', 'status_label', 'battery', 'battery_at', 'notes', 'desa', 'node_name', 'node_code', 'lat', 'lng'] as $f) { $n->$f = $n->$f ?? null; }
        return $n;
    })->all();
    $posko   = $posko ?? [];
    $sNow    = strtoupper($current->status_ai ?? 'UNKNOWN');
    $badgeOf = fn ($s) => match (strtoupper((string) $s)) {
        'AMAN' => 'badge-aman', 'WASPADA' => 'badge-waspada', 'BAHAYA' => 'badge-bahaya', default => 'badge-none',
    };
    $labelOf = fn ($s) => match (strtoupper((string) $s)) {
        'AMAN' => 'Aman', 'WASPADA' => 'Waspada', 'BAHAYA' => 'Bahaya', 'PENDING' => 'Menunggu analisis', default => 'Belum ada data',
    };
    $nodeTotal  = count($nodes);
    $nodeOnline = collect($nodes)->whereNotIn('status', ['UNKNOWN'])->count();
    $lastAgo    = $current?->timestamp ? \Illuminate\Support\Carbon::parse($current->timestamp)->locale('id')->diffForHumans() : null;

    // Grafik mini: kelembapan dangkal 24 data terakhir (skala 0-100%)
    $spark = collect($logs ?? [])->take(-24)->values();
    $pts   = [];
    $n     = max($spark->count() - 1, 1);
    foreach ($spark as $i => $row) {
        $v = max(0, min(100, (float) ($row->soil_moisture ?? 0)));
        $pts[] = round($i * 340 / $n, 1) . ',' . round(110 - $v, 1);
    }
    $polyline = implode(' ', $pts);
    $lastPt   = end($pts) ?: null;
    $area     = $polyline ? '0,115 ' . $polyline . ' 340,115' : '';
    $sparkFirst = $spark->first()?->timestamp ? \Illuminate\Support\Carbon::parse($spark->first()->timestamp)->format('H:i') : '';
@endphp

    <!-- ===================== PITA STATUS ===================== -->
    <div class="bg-air-100 border-b border-air-300/70">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-2.5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 text-[12px]">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white border border-air-300 text-teal-700 font-mono font-semibold uppercase tracking-wide text-[11px]">
                    <span class="material-symbols-outlined text-[15px]">location_on</span> Wilayah pilot: Pacet, Mojokerto
                </span>
                <span class="badge {{ $badgeOf($sNow) }}">Status terkini: {{ $labelOf($sNow) }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-deep-500 font-mono">
                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-teal-600 text-[16px]">sensors</span><strong class="text-deep-900">{{ $nodeOnline }}/{{ $nodeTotal }}</strong> node mengirim data</span>
                <span class="hidden sm:inline-flex items-center gap-1"><span class="material-symbols-outlined text-teal-600 text-[16px]">cell_tower</span>LoRa 920 MHz</span>
                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">schedule</span>Update: {{ $lastAgo ?? 'belum ada data' }}</span>
            </div>
        </div>
    </div>

    <!-- ===================== HERO ===================== -->
    <section id="beranda" class="air-gradient">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-12 pb-16 lg:pt-16 lg:pb-20 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <div class="lg:col-span-7 flex flex-col gap-5 animate-fade-up">
                <span class="chip-outline self-start"><span class="material-symbols-outlined text-[16px]">neurology</span> Inovasi Edge AIoT Mitigasi Longsor · ITS</span>
                <h1 class="text-[2rem] leading-[1.15] sm:text-[2.6rem] lg:text-[3rem] lg:leading-[1.1] font-extrabold text-deep-900 tracking-tight">
                    Peringatan Dini &amp; Navigasi Penyelamatan <span class="text-teal-600">Longsor</span> Berbasis Sensor Cerdas
                </h1>
                <p class="text-[16px] sm:text-[17px] leading-relaxed text-deep-600 max-w-[60ch]">
                    LARAS memantau kelembapan tanah dua kedalaman, curah hujan, dan pergerakan lereng secara langsung, memprediksi status lereng hingga 60 menit ke depan, lalu memandu warga menuju posko evakuasi terdekat.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <button onclick="enterDashboard()" class="group inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-teal-500 hover:bg-teal-600 text-white font-semibold font-heading shadow-md shadow-teal-500/20 transition">
                        Buka Live Monitoring
                        <span class="material-symbols-outlined text-[20px] group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                    </button>
                    <a href="{{ route('peta-evakuasi.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-white border border-air-300 hover:bg-air-100 text-deep-900 font-semibold font-heading transition">
                        <span class="material-symbols-outlined text-[20px] text-teal-600">map</span> Lihat Peta Evakuasi
                    </a>
                </div>
                <div class="info-note p-4 flex flex-col gap-1 max-w-[60ch] mt-1">
                    <span class="text-[12px] font-mono font-semibold uppercase tracking-wider text-teal-700">Jaringan mandiri tanpa sinyal seluler</span>
                    <p class="text-[14px] leading-relaxed text-deep-700">Node sensor saling meneruskan data lewat LoRa mesh 920 MHz ke gateway Raspberry Pi di posko. Analisis AI berjalan langsung di gateway, sehingga peringatan tetap keluar walau internet putus.</p>
                </div>
            </div>

            <!-- Kartu telemetri langsung (data asli dari database) -->
            <div class="lg:col-span-5 animate-fade-up-delay-1">
                <div class="card shadow-lift p-5 sm:p-6 flex flex-col gap-4 relative overflow-hidden">
                    <div class="absolute -top-14 -right-14 w-40 h-40 bg-air-200/60 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-lg bg-air-100 text-teal-600 flex items-center justify-center"><span class="material-symbols-outlined">ssid_chart</span></span>
                            <div>
                                <p class="text-[10px] font-mono font-semibold uppercase tracking-wider text-deep-400">Telemetri terbaru</p>
                                <p class="text-[17px] font-bold font-heading text-deep-900 leading-tight">Kelembapan Tanah</p>
                            </div>
                        </div>
                        <span class="badge {{ $badgeOf($sNow) }}">{{ $labelOf($sNow) }}</span>
                    </div>

                    <div class="relative bg-air-50 border border-air-200 rounded-lg p-3">
                        <div class="flex items-center justify-between text-[11px] font-mono text-deep-400 mb-1">
                            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-1 rounded-full bg-teal-500"></span>Dangkal, {{ $spark->count() }} data terakhir</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-3 border-t border-dashed border-cream-600"></span>Waspada 55%</span>
                        </div>
                        @if($polyline)
                        <svg viewBox="0 0 340 120" preserveAspectRatio="none" class="w-full h-32 overflow-visible" role="img" aria-label="Grafik kelembapan tanah dangkal">
                            <defs><linearGradient id="sparkFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#008080" stop-opacity=".25"/><stop offset="100%" stop-color="#008080" stop-opacity="0"/></linearGradient></defs>
                            <line x1="0" x2="340" y1="35" y2="35" stroke="#BFE3F2" stroke-dasharray="3 3"/>
                            <line x1="0" x2="340" y1="110" y2="110" stroke="#BFE3F2"/>
                            <line x1="0" x2="340" y1="55" y2="55" stroke="#D68F14" stroke-dasharray="5 3" stroke-width="1.2"/>
                            <polygon points="{{ $area }}" fill="url(#sparkFill)"/>
                            <polyline points="{{ $polyline }}" fill="none" stroke="#008080" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                            @if($lastPt)
                                @php [$lx, $ly] = explode(',', $lastPt); @endphp
                                <circle cx="{{ $lx }}" cy="{{ $ly }}" r="8" fill="#008080" fill-opacity=".18"/>
                                <circle cx="{{ $lx }}" cy="{{ $ly }}" r="4.5" fill="#008080"/>
                            @endif
                        </svg>
                        <div class="flex justify-between text-[10px] font-mono text-deep-400 mt-1"><span>{{ $sparkFirst }}</span><span class="text-teal-600 font-semibold">Terbaru</span></div>
                        @else
                        <div class="h-32 flex items-center justify-center text-[13px] text-deep-400">Belum ada data sensor</div>
                        @endif
                    </div>

                    <div class="relative grid grid-cols-2 gap-2.5">
                        @php
                            $mini = [
                                ['water_drop', 'Kelembapan dangkal', $current ? number_format($current->soil_moisture ?? 0, 1) : '–', '%'],
                                ['layers', 'Kelembapan dalam', $current ? number_format($current->moisture_percent_deep ?? 0, 1) : '–', '%'],
                                ['rainy', 'Curah hujan', $current ? number_format($current->rainfall_mm ?? 0, 1) : '–', 'mm/jam'],
                               
                            ];
                        @endphp
                        @foreach($mini as [$ic, $lb, $val, $unit])
                        <div class="rounded-lg bg-air-100/70 border border-air-200 p-3">
                            <p class="text-[11px] text-deep-500 flex items-center gap-1"><span class="material-symbols-outlined text-[15px] text-teal-600">{{ $ic }}</span>{{ $lb }}</p>
                            <p class="mt-1 font-mono text-[20px] font-bold text-deep-900 leading-none">{{ $val }}<span class="text-[11px] font-normal text-deep-400 ml-1">{{ $unit }}</span></p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== PITA ANGKA (URGENSI & KEMAMPUAN) ===================== -->
    <section id="urgensi" class="max-w-[1200px] mx-auto px-4 sm:px-6 -mt-6 relative z-10">
        <span id="features" class="sr-only"></span>
        <div class="rounded-2xl bg-deep-900 text-white shadow-float p-6 sm:p-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:divide-x lg:divide-white/10">
                <div class="lg:pr-6">
                    <p class="font-heading font-extrabold text-[2.2rem] sm:text-[2.6rem] leading-none text-cream-200">330</p>
                    <p class="mt-2 text-[14px] font-semibold">Kejadian longsor 2025</p>
                    <p class="text-[12px] text-deep-200 mt-0.5">naik dari 184 kejadian pada 2024</p>
                </div>
                <div class="lg:px-6">
                    <p class="font-heading font-extrabold text-[2.2rem] sm:text-[2.6rem] leading-none text-cream-200">68.935</p>
                    <p class="mt-2 text-[14px] font-semibold">Jiwa terdampak 2025</p>
                    <p class="text-[12px] text-deep-200 mt-0.5">naik dari 48.344 jiwa pada 2024</p>
                </div>
                <div class="lg:px-6">
                    <p class="font-heading font-extrabold text-[2.2rem] sm:text-[2.6rem] leading-none text-teal-300">+60<span class="text-[1rem] font-bold ml-1">menit</span></p>
                    <p class="mt-2 text-[14px] font-semibold">Prediksi status lereng</p>
                    <p class="text-[12px] text-deep-200 mt-0.5">peringatan sebelum kondisi kritis</p>
                </div>
                <div class="lg:pl-6">
                    <p class="font-heading font-extrabold text-[2.2rem] sm:text-[2.6rem] leading-none text-teal-300">920<span class="text-[1rem] font-bold ml-1">MHz</span></p>
                    <p class="mt-2 text-[14px] font-semibold">LoRa mesh mandiri</p>
                    <p class="text-[12px] text-deep-200 mt-0.5">tidak bergantung sinyal seluler</p>
                </div>
            </div>
            <p class="mt-6 pt-4 border-t border-white/10 text-[12px] text-deep-200 leading-relaxed">
                EWS-L yang ada masih banyak bergantung pada pembacaan manual kader, sehingga rawan jeda peringatan saat malam atau cuaca buruk. LARAS hadir sebagai lapisan digitalisasi tambahan, bukan pengganti sistem eksisting. <span class="font-mono text-deep-300">Sumber data kejadian: BNPB, 2025.</span>
            </p>
        </div>
    </section>

    <!-- ===================== STATUS NODE ===================== -->
    <section id="pantauan" class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
            <div>
                <span class="section-tag">Telemetri real-time</span>
                <h2 class="mt-2 text-[1.75rem] sm:text-[2rem] font-bold text-deep-900">Status Node Sensor di Lereng Pantauan</h2>
                <p class="mt-1 text-[15px] text-deep-500">Status terbaru tiap node dari hasil analisis AI di gateway.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline-flex items-center gap-1.5 text-[12px] font-mono text-deep-500"><span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>Diperbarui otomatis</span>
                <button onclick="enterDashboard()" class="px-3.5 py-2 rounded-lg bg-white border border-air-300 hover:bg-air-100 text-[13px] font-semibold text-teal-700 transition">Detail telemetri</button>
            </div>
        </div>

        @if($nodeTotal)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(array_slice($nodes, 0, 4) as $node)
                @php
                    
                    $st  = strtoupper($node->status ?? 'UNKNOWN');
                    $node->status_label = $node->status_label ?? $labelOf($st);
                    foreach (['battery', 'battery_at', 'notes', 'desa', 'node_name'] as $f) { $node->$f = $node->$f ?? null; }
                    $warn = in_array($st, ['WASPADA', 'BAHAYA'], true);
                    $bat = $node->battery;
                    $batColor = $bat === null ? 'bg-slate-300' : ($bat < 20 ? 'bg-rose-500' : ($bat < 50 ? 'bg-amber-500' : 'bg-emerald-500'));
                @endphp
                <div class="card card-accent p-5 flex flex-col gap-3 {{ $st === 'BAHAYA' ? '!border-rose-300 bg-rose-50/40' : ($st === 'WASPADA' ? '!border-amber-300 bg-amber-50/40' : '') }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-mono font-semibold uppercase tracking-wider {{ $warn ? 'text-amber-700' : 'text-deep-400' }}">{{ $node->node_code }}</span>
                        <span class="badge {{ $badgeOf($st) }}">{{ $node->status_label }}</span>
                    </div>
                    <div>
                        <h3 class="text-[17px] font-bold text-deep-900 leading-snug">{{ $node->node_name ?: $node->node_code }}</h3>
                        <p class="text-[13px] text-deep-500 mt-0.5">{{ $node->desa ?: 'Lokasi belum diisi' }}</p>
                    </div>
                    @if($node->notes)
                        <p class="text-[13px] text-deep-600 leading-relaxed line-clamp-2">{{ $node->notes }}</p>
                    @endif
                   
                </div>
            @endforeach
        </div>
        @if($nodeTotal > 4)
            <p class="mt-4 text-[13px] text-deep-500">Menampilkan 4 dari {{ $nodeTotal }} node. <a href="{{ route('peta-evakuasi.index') }}" class="font-semibold text-teal-600 hover:underline">Lihat semua di Peta Evakuasi →</a></p>
        @endif
        @else
        <div class="card p-8 text-center text-deep-500">Belum ada node sensor terdaftar. Kader dapat menambahkan node di halaman <a href="{{ route('peta-evakuasi.index') }}" class="font-semibold text-teal-600 hover:underline">Peta Evakuasi</a>.</div>
        @endif
    </section>

    <!-- ===================== PETA + POSKO ===================== -->
    <section id="peta" class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <span class="section-tag">Peta &amp; logistik evakuasi</span>
                <h2 class="mt-2 text-[1.75rem] sm:text-[2rem] font-bold text-deep-900">Sebaran Node &amp; Posko Evakuasi</h2>
                <p class="mt-2 text-[14px] text-deep-500 leading-relaxed">Node sensor di lereng beberapa desa Kec. {{ config('laras.kecamatan') }}. Tekan tombol tanda tanya di pojok kanan bawah peta untuk melihat arti setiap simbol.</p>
                <div class="card mt-4 p-2 overflow-hidden">
                    <div id="homeMap" class="w-full h-[340px] sm:h-[400px] rounded-lg bg-air-100"></div>
                </div>
            </div>

            <aside class="lg:col-span-4 lg:pt-[92px]">
                <div class="card p-5 sm:p-6 bg-air-50 h-full flex flex-col gap-4">
                    <div class="flex items-center gap-2 text-teal-700"><span class="material-symbols-outlined">home_health</span><span class="text-[11px] font-mono font-semibold uppercase tracking-wider">Posko evakuasi</span></div>
                    <h3 class="text-[19px] font-bold text-deep-900 -mt-1">Titik kumpul &amp; check-in warga</h3>
                    <ul class="flex flex-col gap-2.5 max-h-[300px] overflow-y-auto pr-1">
                        @forelse($posko as $p)
                        <li class="bg-white border border-air-200 rounded-lg p-3">
                            <p class="text-[14px] font-semibold text-deep-900">{{ $p['nama'] }}</p>
                            <p class="text-[12px] text-deep-500 mt-0.5">{{ $p['lokasi'] }}</p>
                        </li>
                        @empty
                        <li class="text-[13px] text-deep-500">Belum ada posko terdaftar.</li>
                        @endforelse
                    </ul>
                    <p class="text-[13px] text-deep-600 leading-relaxed">Rute evakuasi mengikuti jalan menuju posko terdekat. Setibanya di posko, kader mencatat check-in warga beserta kondisi kesehatannya untuk membantu tim SAR.</p>
                    <a href="{{ route('peta-evakuasi.index') }}" class="mt-auto inline-flex justify-center items-center gap-2 px-4 py-3 rounded-lg bg-teal-500 hover:bg-teal-600 text-white font-semibold font-heading transition">
                        <span class="material-symbols-outlined text-[20px]">route</span> Buka Peta &amp; Rute Evakuasi
                    </a>
                </div>
            </aside>
        </div>
    </section>

    <!-- ===================== CARA KERJA & AI ===================== -->
    <section id="tentang" class="mt-20 bg-air-100/70 border-y border-air-300/60">
        <span id="cara-kerja" class="sr-only"></span><span id="algorithms" class="sr-only"></span>
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-16 sm:py-20">
            <div class="text-center max-w-2xl mx-auto">
                <span class="section-tag">Arsitektur sistem</span>
                <h2 class="mt-2 text-[1.75rem] sm:text-[2rem] font-bold text-deep-900">Dari Sensor Lereng sampai Peringatan Warga</h2>
                <p class="mt-2 text-[15px] text-deep-500">Data mengalir dari node di lapangan, dianalisis di gateway posko, lalu diteruskan ke dashboard, WhatsApp kader, dan warga.</p>
            </div>

            @php
                $flow = [
                    ['sensors', 'Node Sensor', 'ESP32-S3: kelembapan tanah dangkal & dalam, IMU MPU6050, rain gauge'],
                    ['cell_tower', 'LoRa Mesh 920 MHz', 'Antar-node saling meneruskan data + ACK, tanpa sinyal seluler'],
                    ['memory', 'Gateway Raspberry Pi', 'Menerima data di posko, menyimpan lokal saat internet putus'],
                    ['neurology', 'Edge AI', 'Random Forest + LSTM + aturan fisik, langsung di gateway'],
                    ['cloud_sync', 'Cloud', 'Sinkron ke basis data untuk dashboard & riwayat'],
                    ['campaign', 'Peringatan & Navigasi', 'Dashboard, WhatsApp kader, rute ke posko'],
                ];
            @endphp
            <ol class="mt-10 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach($flow as $i => [$ic, $t, $d])
                <li class="card p-4 relative">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-lg bg-air-100 border border-air-300 text-teal-600 flex items-center justify-center"><span class="material-symbols-outlined">{{ $ic }}</span></span>
                        <span class="font-mono text-[11px] font-semibold text-deep-300">0{{ $i + 1 }}</span>
                    </div>
                    <p class="mt-3 text-[14px] font-bold text-deep-900 font-heading leading-snug">{{ $t }}</p>
                    <p class="mt-1 text-[12px] text-deep-500 leading-relaxed">{{ $d }}</p>
                </li>
                @endforeach
            </ol>

            <div class="mt-14 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-4">
                    <span class="section-tag">Triase AI 3 lapis</span>
                    <h3 class="mt-2 text-[1.5rem] font-bold text-deep-900 leading-tight">Tiga lapisan yang saling mengoreksi</h3>
                    <p class="mt-2 text-[15px] text-deep-600 leading-relaxed">Status akhir diambil dari jalur yang paling parah. Skor kerentanan dari survei lanskap hanya boleh menaikkan status, tidak pernah menurunkan.</p>
                    <div class="info-note p-4 mt-5">
                        <p class="text-[12px] font-mono font-semibold uppercase tracking-wider text-teal-700">Human in the loop</p>
                        <p class="text-[14px] text-deep-700 mt-1 leading-relaxed">Status <strong>BAHAYA</strong> ditandai "perlu verifikasi kader" dan dikirim ke kader lebih dulu sebelum disebarkan ke warga.</p>
                    </div>
                </div>
                <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $layers = [
                            ['forest', 'Lapis 1', 'Random Forest', 'Memprediksi status AMAN / WASPADA / BAHAYA 60 menit ke depan dari kelembapan tanah dan akumulasi hujan ("piezometer virtual").', '200 pohon · +60 menit'],
                            ['timeline', 'Lapis 2', 'LSTM + MC Dropout', 'Meramal kebasahan tanah 1 jam ke depan. Monte Carlo Dropout 30× mengukur ketidakpastian; prediksi ragu dinaikkan ke WASPADA.', 'Window 3 jam · +1 jam'],
                            ['rule', 'Lapis 3', 'Ambang Fisik', 'Kurva intensitas–durasi hujan (Caine, 1980; Segoni dkk., 2018) dan gerak lereng dari IMU sebagai jaring pengaman independen.', 'Hujan · getaran · kemiringan'],
                        ];
                    @endphp
                    @foreach($layers as [$ic, $lv, $t, $d, $meta])
                    <div class="card card-accent p-5 flex flex-col">
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-lg bg-air-100 border border-air-300 text-teal-600 flex items-center justify-center"><span class="material-symbols-outlined">{{ $ic }}</span></span>
                            <span class="badge badge-cream">{{ $lv }}</span>
                        </div>
                        <h4 class="mt-4 text-[16px] font-bold text-deep-900">{{ $t }}</h4>
                        <p class="mt-1.5 text-[13px] text-deep-600 leading-relaxed">{{ $d }}</p>
                        <p class="mt-auto pt-4 text-[11px] font-mono font-semibold text-teal-700">{{ $meta }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== PERAN PENGGUNA ===================== -->
    <section id="peran" class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-20">
        <div class="text-center max-w-2xl mx-auto">
            <span class="section-tag">Multi-pengguna</span>
            <h2 class="mt-2 text-[1.75rem] sm:text-[2rem] font-bold text-deep-900">Akses Sesuai Peran</h2>
            <p class="mt-2 text-[15px] text-deep-500">Warga cukup membuka website tanpa login. Kader dan admin masuk untuk mengelola data lapangan.</p>
        </div>
        @php
            $roles = [
                ['groups', 'Peran 01', 'Warga', 'Akses publik', 'badge-aman', [
                    ['monitoring', 'Melihat status lereng & grafik sensor terkini'],
                    ['map', 'Peta node, rute evakuasi, dan rekap posko'],
                    ['call', 'Edukasi longsor & kontak darurat 112'],
                ]],
                ['shield_person', 'Peran 02', 'Kader Desa', 'Petugas lapangan', 'badge-cream', [
                    ['notifications_active', 'Menerima peringatan WhatsApp & verifikasi lapangan'],
                    ['landscape', 'Mengisi survei Data Lanskap per node'],
                    ['how_to_reg', 'Mencatat check-in warga & lokasi node (GPS)'],
                ]],
                ['admin_panel_settings', 'Peran 03', 'Admin', 'Kontrol penuh', 'badge-none', [
                    ['person_add', 'Membuat akun kader & admin'],
                    ['fact_check', 'Menyetujui atau menolak pendaftar baru'],
                    ['tune', 'Semua hak kader'],
                ]],
            ];
        @endphp
        <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($roles as [$ic, $no, $title, $tag, $bc, $items])
            <div class="card card-accent p-6">
                <div class="flex items-center justify-between">
                    <span class="w-11 h-11 rounded-lg bg-air-100 border border-air-300 text-teal-600 flex items-center justify-center"><span class="material-symbols-outlined">{{ $ic }}</span></span>
                    <span class="badge {{ $bc }} badge-plain">{{ $tag }}</span>
                </div>
                <p class="mt-5 text-[11px] font-mono font-semibold uppercase tracking-wider text-deep-400">{{ $no }}</p>
                <h3 class="text-[20px] font-bold text-deep-900">{{ $title }}</h3>
                <ul class="mt-4 flex flex-col gap-2.5">
                    @foreach($items as [$iic, $txt])
                    <li class="flex items-start gap-2.5 text-[14px] text-deep-600"><span class="material-symbols-outlined text-[18px] text-teal-600 mt-0.5">{{ $iic }}</span>{{ $txt }}</li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </section>

    <!-- ===================== EDUKASI + KONTAK DARURAT ===================== -->
    <section id="edukasi" class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7">
                <span class="section-tag">Edukasi kebencanaan</span>
                <h2 class="mt-2 text-[1.75rem] sm:text-[2rem] font-bold text-deep-900">Tanda-Tanda &amp; Penanganan Longsor</h2>
                <p class="mt-2 text-[14px] text-deep-500">Panduan umum. Karakteristik tiap wilayah bisa berbeda, ikuti arahan BPBD dan kader setempat.</p>
                @php
                    $edu = [
                        ['warning', 'Kenali tanda-tanda awal', true, 'text-amber-500', [
                            'Muncul retakan baru di tanah atau tembok, terutama sejajar lereng.',
                            'Air rembesan atau mata air baru di lereng, atau air sumur tiba-tiba keruh.',
                            'Pohon, tiang listrik, atau pagar mulai miring ke satu arah.',
                            'Suara gemuruh dari arah lereng saat/setelah hujan deras berkepanjangan.',
                            'Pintu atau jendela rumah di lereng tiba-tiba sulit dibuka/ditutup.',
                        ]],
                        ['backpack', 'Sebelum terjadi (kesiapsiagaan)', false, 'text-teal-600', [
                            'Kenali rute evakuasi dan posko terdekat di Peta Evakuasi LARAS.',
                            'Siapkan tas siaga: dokumen penting, senter, obat, air minum, uang tunai secukupnya.',
                            'Pantau status LARAS dan informasi BMKG saat musim hujan.',
                        ]],
                        ['directions_run', 'Saat dan setelah kejadian', false, 'text-rose-500', [
                            'Saat status BAHAYA, segera menuju posko lewat rute yang sudah ditentukan, jangan ambil jalan pintas.',
                            'Dahulukan lansia, anak-anak, dan penyandang disabilitas.',
                            'Lakukan check-in di posko agar tim SAR tahu siapa saja yang sudah aman.',
                            'Jangan kembali ke lokasi terdampak sebelum ada pernyataan aman dari BPBD/kader.',
                        ]],
                    ];
                @endphp
                <div class="mt-6 flex flex-col gap-3">
                    @foreach($edu as [$ic, $t, $open, $dot, $list])
                    <details class="edu-accordion card p-5" @if($open) open @endif>
                        <summary class="flex items-center justify-between gap-3 font-semibold text-deep-900 font-heading">
                            <span class="flex items-center gap-3"><span class="w-9 h-9 rounded-lg bg-air-100 text-teal-600 flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">{{ $ic }}</span></span>{{ $t }}</span>
                            <span class="material-symbols-outlined chev transition-transform text-deep-400">expand_more</span>
                        </summary>
                        <ul class="mt-4 space-y-2 text-[14px] text-deep-600 pl-1">
                            @foreach($list as $li)<li class="flex items-start gap-2.5"><span class="{{ $dot }} mt-[7px] w-1.5 h-1.5 rounded-full bg-current shrink-0"></span>{{ $li }}</li>@endforeach
                        </ul>
                    </details>
                    @endforeach
                </div>
            </div>

            <div id="kontak-darurat" class="lg:col-span-5">
                <span class="section-tag">Darurat</span>
                <h2 class="mt-2 text-[1.75rem] sm:text-[2rem] font-bold text-deep-900">Kontak Darurat</h2>
                <p class="mt-2 text-[14px] text-deep-500">LARAS adalah alat bantu, bukan pengganti prosedur resmi.</p>
                <a href="tel:112" class="mt-6 flex items-center gap-4 p-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-lg shadow-rose-600/20 transition">
                    <span class="w-12 h-12 rounded-full bg-white/15 flex items-center justify-center"><span class="material-symbols-outlined text-[26px]">call</span></span>
                    <span>
                        <span class="block font-mono text-[28px] font-bold leading-none">112</span>
                        <span class="block text-[13px] text-white/85 mt-1">Panggilan darurat gratis, bisa saat HP terkunci</span>
                    </span>
                </a>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @php
                        $kontak = [['117', 'BNPB', 'Koordinasi bencana nasional', 'tel:117'], ['115', 'Basarnas', 'Pencarian & evakuasi (SAR)', 'tel:115'],
                                   ['(031) 99860466', 'BPBD setempat', 'Wilayah pilot LARAS', 'tel:03199860466'], ['081245789740', 'Kader / posko desa', 'Nomor kader EWS-L', null]];
                    @endphp
                    @foreach($kontak as [$num, $who, $desc, $href])
                    <a @if($href) href="{{ $href }}" @endif class="card p-4 {{ $href ? 'card-accent' : 'border-dashed' }}">
                        <p class="font-mono font-bold text-teal-700 {{ strlen($num) > 6 ? 'text-[15px]' : 'text-[22px]' }} leading-tight">{{ $num }}</p>
                        <p class="mt-1 text-[14px] font-semibold text-deep-900">{{ $who }}</p>
                        <p class="text-[12px] text-deep-500">{{ $desc }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== TIM ===================== -->
    <section id="developers" class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-8">
            <div>
                <span class="section-tag">Tim pengembang</span>
                <h2 class="mt-2 text-[1.75rem] sm:text-[2rem] font-bold text-deep-900">Dikembangkan di Institut Teknologi Sepuluh Nopember</h2>
            </div>
            <p class="text-[14px] text-deep-500 md:text-right">Departemen Teknik Komputer · ITS Surabaya</p>
        </div>
        @php
            $devs = [
                ['Devi Putri Sekar Arum', '5024241049', 'Machine Learning & AI Engineer'],
                ['Athaya Khairani Adi', '5024241007', 'Web Developer & Software Engineer'],
                ['Atika Najwa Azzahra', '5024241091', 'Hardware Engineer & Prototype Designer'],
                ['Ir. Arta Kusuma Hernanda, S.T., M.T.', 'Dosen Pembimbing', 'Pembimbing Tim'],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($devs as $i => [$name, $id, $role])
            <div class="card p-5 flex items-center gap-4">
                <span class="w-12 h-12 shrink-0 rounded-full {{ $i === 3 ? 'bg-deep-900 text-cream-200' : 'bg-air-100 text-teal-700 border border-air-300' }} flex items-center justify-center font-heading font-bold text-[18px]">{{ mb_substr($name, $i === 3 ? 4 : 0, 1) }}</span>
                <div class="min-w-0">
                    <p class="text-[14px] font-bold text-deep-900 leading-snug">{{ $name }}</p>
                    <p class="text-[11px] font-mono text-deep-400">{{ $id }}</p>
                    <p class="text-[12px] text-deep-600 mt-0.5">{{ $role }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- ===================== CTA ===================== -->
    <section class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-20">
        <div class="rounded-2xl bg-air-100 border border-air-300 p-6 sm:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute -right-16 -bottom-20 w-64 h-64 rounded-full bg-air-200/70 pointer-events-none"></div>
            <div class="relative max-w-2xl">
                <span class="chip-outline"><span class="material-symbols-outlined text-[15px]">verified</span> Riset Teknik Komputer ITS</span>
                <h2 class="mt-3 text-[1.6rem] sm:text-[2rem] font-bold text-deep-900 leading-tight">Pantau kondisi lereng secara real-time</h2>
                <p class="mt-2 text-[15px] text-deep-600">Buka Live Monitoring untuk melihat data sensor terbaru, prediksi AI, cuaca BMKG, dan bertanya ke LARAS AI Assistant.</p>
            </div>
            <div class="relative flex flex-wrap gap-3">
                <button onclick="enterDashboard()" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-teal-500 hover:bg-teal-600 text-white font-semibold font-heading shadow-md transition">
                    <span class="material-symbols-outlined text-[20px]">monitoring</span> Live Monitoring
                </button>
                <a href="#edukasi" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-white border border-air-300 hover:bg-air-50 text-deep-900 font-semibold font-heading transition">Baca Edukasi</a>
            </div>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="mt-20 bg-deep-900 text-white">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-xl bg-white flex items-center justify-center overflow-hidden"><img src="/images/logo_laras.png" alt="Logo LARAS" class="w-8 h-8 object-contain"></span>
                    <span class="font-heading font-bold text-[18px]">LARAS</span>
                </div>
                <p class="mt-3 text-[13px] text-deep-200 leading-relaxed">Landslide Alert and Rescue Adaptive System. Sistem peringatan dini dan navigasi evakuasi longsor berbasis Edge AIoT.</p>
            </div>
            <div>
                <p class="text-[11px] font-mono font-semibold uppercase tracking-wider text-cream-200">Navigasi</p>
                <ul class="mt-3 space-y-2 text-[14px] text-deep-100">
                    <li><a href="#beranda" class="hover:text-white">Beranda</a></li>
                    <li><a href="javascript:enterDashboard()" class="hover:text-white">Live Monitoring</a></li>
                    <li><a href="{{ route('peta-evakuasi.index') }}" class="hover:text-white">Peta Evakuasi</a></li>
                    <li><a href="{{ route('lanskap.index') }}" class="hover:text-white">Data Lanskap</a></li>
                    <li><a href="#edukasi" class="hover:text-white">Edukasi</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[11px] font-mono font-semibold uppercase tracking-wider text-cream-200">Darurat 24 jam</p>
                <ul class="mt-3 space-y-2 text-[14px] text-deep-100">
                    <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-cream-200">call</span><a href="tel:112" class="font-mono hover:text-white">112 (bebas pulsa)</a></li>
                    <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-cream-200">support_agent</span><a href="tel:117" class="font-mono hover:text-white">BNPB 117</a></li>
                    <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-cream-200">sos</span><a href="tel:115" class="font-mono hover:text-white">Basarnas 115</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[11px] font-mono font-semibold uppercase tracking-wider text-cream-200">Institusi</p>
                <p class="mt-3 text-[14px] text-deep-100 leading-relaxed">Departemen Teknik Komputer<br>Institut Teknologi Sepuluh Nopember<br>Surabaya, Jawa Timur</p>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row justify-between gap-2 text-[12px] text-deep-300">
                <span>© {{ date('Y') }} LARAS · Institut Teknologi Sepuluh Nopember</span>
                <span class="font-mono inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full {{ $lastAgo ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>Data terakhir: {{ $lastAgo ?? '–' }}</span>
            </div>
        </div>
    </footer>

    <!-- Peta mini Beranda (Leaflet): ikon gambar, tanpa emoji, legenda lewat tombol "?" -->
    <style>
        @keyframes larasPulse { 0% { transform: scale(.6); opacity: .55; } 100% { transform: scale(1.9); opacity: 0; } }
        .posko-marker { border-radius: 9999px; border: 3px solid #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, .45); background: #fff; }
        .leaflet-control.legenda-wrap { border: 0; background: transparent; box-shadow: none; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .legenda-btn { width: 44px; height: 44px; border-radius: 9999px; background: #0f766e; border: 3px solid #fff; box-shadow: 0 4px 12px rgba(15, 23, 42, .35); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform .15s, background .15s; }
        .legenda-btn:hover { background: #115e59; transform: scale(1.06); }
        .legenda-btn img { width: 22px; height: 22px; pointer-events: none; }
        .legenda-panel { display: none; width: 270px; max-width: calc(100vw - 64px); max-height: 255px; overflow-y: auto; background: #fff; border: 1px solid #cfe5ea; border-radius: 14px; padding: 14px 14px 10px; box-shadow: 0 10px 30px rgba(15, 23, 42, .25); font-family: Inter, sans-serif; color: #0f172a; cursor: default; }
        .legenda-panel.terbuka { display: block; }
        .legenda-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        .legenda-head h4 { font-size: 14px; font-weight: 700; margin: 0; }
        .legenda-head button { width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .legenda-head button:hover { background: #f1f5f9; }
        .legenda-head button img { width: 16px; height: 16px; }
        .legenda-grup { font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #64748b; margin: 10px 0 4px; }
        .legenda-item { display: flex; align-items: flex-start; gap: 10px; padding: 5px 0; }
        .legenda-item .simbol { width: 28px; flex-shrink: 0; display: flex; justify-content: center; padding-top: 1px; }
        .legenda-item .simbol img { width: 24px; height: 24px; }
        .legenda-item .titik { width: 16px; height: 16px; border-radius: 9999px; border: 3px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); margin-top: 3px; }
        .legenda-item b { display: block; font-size: 12.5px; }
        .legenda-item span { display: block; font-size: 11.5px; color: #475569; line-height: 1.4; }
    </style>
    <script>
        (function () {
            const nodes = @json(collect($nodes)->filter(fn ($n) => $n->lat !== null && $n->lng !== null)->values());
            const posko = @json($posko);
            const stasiun = @json(config('laras.stasiun'));
            const colors = { AMAN: '#10B981', WASPADA: '#F59E0B', BAHAYA: '#E11D48' };
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const fmt = (v, u) => (v === null || v === undefined) ? '-' : `${v}${u}`;

            // Titik node: lingkaran warna status + nama node; BAHAYA berdenyut
            function nodeIcon(n) {
                const c = colors[n.status] || '#94A3B8';
                const pulse = n.status === 'BAHAYA'
                    ? `<span style="position:absolute;inset:-9px;border-radius:50%;background:${c};opacity:.35;animation:larasPulse 1.6s ease-out infinite"></span>` : '';
                return L.divIcon({
                    className: '',
                    html: `<div style="position:relative;width:20px;height:20px">${pulse}
                             <div style="position:relative;background:${c};width:20px;height:20px;border-radius:50%;border:3px solid #fff;box-shadow:0 1px 5px rgba(0,0,0,.45)"></div>
                             <span style="position:absolute;left:24px;top:1px;white-space:nowrap;font:700 10.5px Inter,sans-serif;color:#0f172a;background:rgba(255,255,255,.9);padding:1px 6px;border-radius:6px;box-shadow:0 1px 2px rgba(0,0,0,.2)">${esc(n.node_name || n.node_code)}</span>
                           </div>`,
                    iconSize: [20, 20], iconAnchor: [10, 10], popupAnchor: [0, -12],
                });
            }
            // Posko: gambar tenda (file gambar, bukan emoji)
            const poskoIcon = () => L.icon({ iconUrl: '/images/icons/posko.webp', iconSize: [34, 34], iconAnchor: [17, 17], popupAnchor: [0, -18], className: 'posko-marker' });

            const LEGENDA_HTML = `
                <div class="legenda-head">
                    <h4>Arti Simbol di Peta</h4>
                    <button type="button" class="legenda-tutup" aria-label="Tutup legenda"><img src="/images/icons/tutup.svg" alt=""></button>
                </div>
                <p class="legenda-grup">Status node sensor</p>
                <div class="legenda-item"><div class="simbol"><span class="titik" style="background:#10b981"></span></div><div><b>Hijau - Aman</b><span>Kondisi lereng normal.</span></div></div>
                <div class="legenda-item"><div class="simbol"><span class="titik" style="background:#f59e0b"></span></div><div><b>Oranye - Waspada</b><span>Tanah mulai jenuh air atau bergerak. Bersiap mengungsi.</span></div></div>
                <div class="legenda-item"><div class="simbol"><span class="titik" style="background:#e11d48"></span></div><div><b>Merah - Bahaya</b><span>Risiko longsor tinggi. Segera mengungsi ke posko.</span></div></div>
                <div class="legenda-item"><div class="simbol"><span class="titik" style="background:#94a3b8"></span></div><div><b>Abu-abu - Belum ada data</b><span>Node belum mengirim data terbaru.</span></div></div>
                <div class="legenda-item"><div class="simbol"><img src="/images/icons/stasiun.svg" alt=""></div><div><b>Ungu - Stasiun utama</b><span>Raspberry Pi: menerima data semua node dan menjalankan analisis AI.</span></div></div>
                <p class="legenda-grup">Evakuasi</p>
                <div class="legenda-item"><div class="simbol"><img src="/images/icons/posko.webp" alt="" style="border-radius:9999px"></div><div><b>Posko evakuasi</b><span>Tempat mengungsi. Rute lengkap ada di halaman Peta Evakuasi.</span></div></div>`;

            // dibuat saat init(): Leaflet dimuat setelah skrip ini
            const buatLegenda = () => new (L.Control.extend({
                options: { position: 'bottomright' },
                onAdd: function () {
                    const wrap = L.DomUtil.create('div', 'leaflet-control legenda-wrap');
                    const panel = L.DomUtil.create('div', 'legenda-panel', wrap);
                    panel.innerHTML = LEGENDA_HTML;
                    const btn = L.DomUtil.create('a', 'legenda-btn', wrap);
                    btn.href = '#'; btn.title = 'Arti simbol di peta';
                    btn.setAttribute('aria-label', 'Tampilkan legenda peta');
                    btn.innerHTML = '<img src="/images/icons/bantuan.svg" alt="">';
                    L.DomEvent.disableClickPropagation(wrap);
                    L.DomEvent.disableScrollPropagation(wrap);
                    L.DomEvent.on(btn, 'click', e => { L.DomEvent.preventDefault(e); panel.classList.toggle('terbuka'); });
                    L.DomEvent.on(panel.querySelector('.legenda-tutup'), 'click', () => panel.classList.remove('terbuka'));
                    return wrap;
                },
            }))();

            function init() {
                const el = document.getElementById('homeMap');
                if (!el || typeof L === 'undefined') { if (el) el.innerHTML = '<div class="h-full flex items-center justify-center text-sm text-deep-400">Peta tidak dapat dimuat.</div>'; return; }
                const map = L.map(el, { scrollWheelZoom: false, attributionControl: true });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
                const pts = [];
                nodes.forEach(n => {
                    const c = colors[n.status] || '#94A3B8';
                    L.marker([n.lat, n.lng], { icon: nodeIcon(n), zIndexOffset: n.status === 'BAHAYA' ? 1000 : 500 }).addTo(map)
                        .bindPopup(`<div style="font-family:Inter,sans-serif;min-width:180px">
                            <p style="font-weight:700;font-size:13px;margin:0 0 2px">${esc(n.node_name || n.node_code)}</p>
                            <p style="font-size:11px;color:#64748b;margin:0 0 6px">Desa ${esc(n.desa || '-')}</p>
                            <span style="font-size:10px;font-weight:700;color:#fff;background:${c};padding:2px 8px;border-radius:999px">${esc(n.status_label)}</span>
                            <p style="font-size:11px;color:#475569;margin:8px 0 0">Tanah dangkal ${fmt(n.moisture, '%')} | dalam ${fmt(n.moisture_deep, '%')}<br>Hujan ${fmt(n.rain, ' mm')} | Kemiringan ${fmt(n.tilt, '°')}</p>
                            ${n.last_at ? `<p style="font-size:10px;color:#94a3b8;margin:4px 0 0">Data ${esc(n.last_at)}</p>` : ''}
                        </div>`);
                    pts.push([n.lat, n.lng]);
                });
                posko.forEach(p => {
                    L.marker([p.lat, p.lng], { icon: poskoIcon() }).addTo(map)
                        .bindPopup(`<div style="font-family:Inter,sans-serif"><b>${esc(p.nama)}</b><br><span style="font-size:11px;color:#64748b">${esc(p.lokasi)}</span>${p.kapasitas ? `<br><span style="font-size:11px">Kapasitas ${esc(p.kapasitas)} orang</span>` : ''}</div>`);
                    // posko dalam radius 2 km dari stasiun ikut dibingkai; yang jauh tetap tampil
                    if (!stasiun || L.latLng(stasiun.lat, stasiun.lng).distanceTo([p.lat, p.lng]) <= 2000) pts.push([p.lat, p.lng]);
                });
                if (stasiun) {   // stasiun utama (Raspberry Pi gateway): penanda ungu
                    L.marker([stasiun.lat, stasiun.lng], { zIndexOffset: 800,
                        icon: L.icon({ iconUrl: '/images/icons/stasiun.svg', iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16] }) })
                        .addTo(map).bindPopup(`<div style="font-family:Inter,sans-serif"><b style="color:#6d28d9">${esc(stasiun.nama)}</b><br><span style="font-size:11px;color:#64748b">${esc(stasiun.ket)}</span></div>`);
                    pts.push([stasiun.lat, stasiun.lng]);
                }
                map.addControl(buatLegenda());
                const bingkai = () => { map.invalidateSize(); if (pts.length) map.fitBounds(pts, { padding: [30, 30], maxZoom: 16 }); else map.setView([-7.661646, 112.516094], 15); };
                bingkai();
                let petaDisentuh = false;   // setelah peta disentuh, posisi & zoom pengguna tidak diganggu
                ['pointerdown', 'wheel', 'touchstart', 'keydown'].forEach(ev =>
                    map.getContainer().addEventListener(ev, () => { petaDisentuh = true; }, { once: true, passive: true }));
                if (document.readyState !== 'complete') window.addEventListener('load', () => { if (!petaDisentuh) bingkai(); }, { once: true });
                if (window.ResizeObserver) new ResizeObserver(() => { if (!petaDisentuh) bingkai(); }).observe(map.getContainer());
                window.larasHomeMap = map;
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
        })();
    </script>
</div>

<div id="dashboard-page" style="display:none;">


    <main class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 md:py-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <span class="section-tag">Live Monitoring</span>
                <h1 class="mt-1 text-[1.6rem] sm:text-[1.9rem] font-bold text-deep-900">Pantauan Lereng Pacet</h1>
            </div>
            <div class="inline-flex self-start sm:self-auto p-1 rounded-xl bg-air-100 border border-air-300" role="group" aria-label="Pilih tampilan">
                <button type="button" data-view-btn="warga" onclick="setLiveView('warga')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[14px] font-semibold transition">
                    <span class="material-symbols-outlined text-[18px]">groups</span> Untuk Warga
                </button>
                <button type="button" data-view-btn="teknis" onclick="setLiveView('teknis')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[14px] font-semibold transition">
                    <span class="material-symbols-outlined text-[18px]">monitoring</span> Data Teknis
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <div class="lg:col-span-8 space-y-6">
                {{-- ================= MODE WARGA (bahasa awam) ================= --}}
                <div id="wargaMain" class="space-y-5">

                    {{-- 1. STATUS BESAR + APA YANG HARUS DILAKUKAN --}}
                    <section id="wStatusCard" class="rounded-2xl border-2 p-5 sm:p-7 bg-white border-air-300">
                        <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-5">
                            <div id="wStatusIcon" class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-2xl flex items-center justify-center bg-slate-100 text-slate-500">
                                <span class="material-symbols-outlined text-[40px] sm:text-[48px]">help</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-mono font-semibold uppercase tracking-wider text-deep-400">Kondisi lereng saat ini</p>
                                <h2 id="wStatusTitle" class="mt-1 text-[1.9rem] sm:text-[2.4rem] leading-tight font-extrabold text-deep-900">Memuat…</h2>
                                <p id="wStatusDesc" class="mt-2 text-[16px] sm:text-[17px] leading-relaxed text-deep-700"></p>
                                <p id="wUpdated" class="mt-3 inline-flex items-center gap-1.5 text-[13px] text-deep-500">
                                    <span class="material-symbols-outlined text-[18px]">schedule</span><span>—</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-xl bg-white/70 border border-current/10 p-4 sm:p-5" style="border-color: rgba(19,51,50,.08)">
                            <p class="text-[15px] font-bold text-deep-900 flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-teal-600">checklist</span>Yang perlu Anda lakukan</p>
                            <ol id="wTodo" class="mt-3 space-y-2.5 text-[15px] text-deep-700"></ol>
                        </div>

                        <div id="wActions" class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ route('peta-evakuasi.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-teal-500 hover:bg-teal-600 text-white text-[15px] font-semibold font-heading transition">
                                <span class="material-symbols-outlined text-[20px]">route</span> Lihat rute &amp; posko evakuasi
                            </a>
                            <a id="wCall112" href="tel:112" class="hidden items-center gap-2 px-5 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-[15px] font-semibold font-heading transition">
                                <span class="material-symbols-outlined text-[20px]">call</span> Telepon darurat 112
                            </a>
                        </div>
                    </section>

                    {{-- 2. PERKIRAAN 1 JAM KE DEPAN --}}
                    <section class="card p-5 flex items-start gap-4">
                        <span class="w-12 h-12 shrink-0 rounded-xl bg-air-100 border border-air-300 text-teal-600 flex items-center justify-center"><span class="material-symbols-outlined text-[26px]">schedule</span></span>
                        <div class="min-w-0">
                            <p class="text-[13px] text-deep-500">Perkiraan 1 jam ke depan (hasil analisis komputer)</p>
                            <p id="wForecast" class="mt-0.5 text-[18px] sm:text-[20px] font-bold text-deep-900 font-heading">Sedang dihitung…</p>
                            <p id="wForecastNote" class="mt-1 text-[14px] text-deep-600 leading-relaxed"></p>
                        </div>
                    </section>

                    {{-- 3. KONDISI LERENG DALAM BAHASA SEHARI-HARI --}}
                    <section>
                        <div class="flex items-end justify-between gap-3 mb-3">
                            <div>
                                <h3 class="text-[19px] font-bold text-deep-900">Apa yang dirasakan sensor di lereng?</h3>
                                <p class="text-[14px] text-deep-500">Diperbarui otomatis setiap 10 detik.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @php
                                $wCards = [
                                    ['wSoil',  'water_drop',      'Air di tanah permukaan', 'Seberapa basah tanah di dekat permukaan (±25 cm).'],
                                    ['wDeep',  'layers',          'Air di tanah bagian dalam', 'Seberapa basah tanah lebih dalam (±75 cm). Kalau ikut basah, lereng makin berat.'],
                                    ['wRain',  'rainy',           'Hujan di lereng', 'Diukur oleh alat penakar hujan di lokasi.'],
                                    ['wMove',  'landslide',       'Gerakan tanah', 'Apakah tiang sensor bergeser atau miring dibanding sebelumnya.'],
                                ];
                            @endphp
                            @foreach($wCards as [$id, $ic, $title, $help])
                            <div id="{{ $id }}" class="card p-5 flex flex-col gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-11 h-11 shrink-0 rounded-xl bg-air-100 border border-air-300 text-teal-600 flex items-center justify-center"><span class="material-symbols-outlined text-[24px]">{{ $ic }}</span></span>
                                    <p class="text-[15px] font-semibold text-deep-900 leading-snug">{{ $title }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span data-w="dot" class="w-3 h-3 rounded-full bg-slate-300"></span>
                                    <p data-w="word" class="text-[22px] font-extrabold font-heading text-deep-900 leading-none">—</p>
                                </div>
                                <div data-w="bar" class="hidden">
                                    <div class="relative h-2.5 rounded-full overflow-hidden flex">
                                        <span class="h-full bg-emerald-200" style="width:55%"></span><span class="h-full bg-amber-200" style="width:20%"></span><span class="h-full bg-rose-200" style="width:25%"></span>
                                        <span data-w="pin" class="absolute top-1/2 -translate-y-1/2 w-3.5 h-3.5 rounded-full bg-deep-900 border-2 border-white shadow" style="left:0%"></span>
                                    </div>
                                    <div class="mt-1 flex justify-between text-[11px] text-deep-400"><span>Kering</span><span>Basah</span><span>Jenuh</span></div>
                                </div>
                                <p data-w="mean" class="text-[14px] text-deep-700 leading-relaxed"></p>
                                <p class="mt-auto pt-2 border-t border-air-200 text-[12px] text-deep-400 leading-relaxed">{{ $help }} <span data-w="raw" class="font-mono"></span></p>
                            </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- 4. PERUBAHAN 1 JAM TERAKHIR --}}
                    <section class="info-note p-5">
                        <p class="text-[15px] font-bold text-deep-900 flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-teal-600">history</span>Perubahan dalam 1 jam terakhir</p>
                        <ul id="wTrend" class="mt-2 space-y-1.5 text-[15px] text-deep-700"></ul>
                    </section>

                    {{-- 5. ARTI WARNA STATUS --}}
                    <section class="card p-5">
                        <p class="text-[15px] font-bold text-deep-900">Arti warna status</p>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4">
                                <p class="font-bold text-emerald-800 flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span>AMAN</p>
                                <p class="mt-1 text-[14px] text-emerald-900/80 leading-relaxed">Lereng stabil. Beraktivitas seperti biasa.</p>
                            </div>
                            <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
                                <p class="font-bold text-amber-800 flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span>WASPADA</p>
                                <p class="mt-1 text-[14px] text-amber-900/80 leading-relaxed">Tanah jenuh air atau mulai bergerak. Bersiap mengungsi.</p>
                            </div>
                            <div class="rounded-xl bg-rose-50 border border-rose-200 p-4">
                                <p class="font-bold text-rose-800 flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500"></span>BAHAYA</p>
                                <p class="mt-1 text-[14px] text-rose-900/80 leading-relaxed">Risiko longsor tinggi. Segera ke posko evakuasi.</p>
                            </div>
                        </div>
                        <p class="mt-3 text-[13px] text-deep-500">LARAS adalah alat bantu. Selalu ikuti arahan kader desa dan petugas BPBD setempat.</p>
                    </section>
                </div>
                {{-- ================= /MODE WARGA ================= --}}

                <div id="teknisMain" class="space-y-6">

                @php
                    $status = strtoupper($current->status_ai ?? 'UNKNOWN');
                    $isBahaya = $status === 'BAHAYA';
                    $isWaspada = $status === 'WASPADA';
                    if ($isBahaya) {
                        $statusBg = 'bg-rose-50 border-rose-500';
                        $statusTextColor = 'text-rose-700';
                        $statusLabel = 'BAHAYA : EVAKUASI SEGERA';
                    } elseif ($isWaspada) {
                        $statusBg = 'bg-yellow-50 border-yellow-500';
                        $statusTextColor = 'text-yellow-700';
                        $statusLabel = 'WASPADA : SIAGA PENUH';
                    } else {
                        $statusBg = 'bg-white border-emerald-500';
                        $statusTextColor = 'text-emerald-700';
                        $statusLabel = 'SISTEM AMAN';
                    }
                @endphp

                <div id="statusCard" class="transition-all duration-300 rounded-2xl shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between border-l-4 gap-4 {{ $statusBg }}">
                    <div>
                        <h2 class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Status Kondisi Sistem
                        </h2>
                        <p id="statusText" class="text-3xl font-extrabold tracking-tight {{ $statusTextColor }}">{{ $statusLabel }}</p>
                        <div class="flex items-center gap-3 mt-1 text-xs text-slate-500">
                            <span>Status DB: <strong class="font-mono text-slate-700">{{ $status }}</strong></span>
                        </div>
                    </div>
                    <div class="md:border-l md:border-slate-200/60 md:pl-6 flex flex-col items-start md:items-end gap-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Last Telemetry Update</p>
                        <p id="lastUpdate" class="font-mono text-sm md:text-base font-semibold text-slate-700 flex items-center md:justify-end gap-1.5">
                            <span class="h-2 w-2 rounded-full bg-blue-500 inline-block"></span>
                            {{ $current->timestamp ?? '-' }}
                        </p>
                        @if($isBahaya || $isWaspada)
                        <a href="tel:112" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                            Hubungi Darurat
                        </a>
                        @endif
                    </div>
                </div>

                @if($isBahaya)
                <div id="humanInLoopBanner" class="flex items-start gap-3 bg-rose-50 border border-rose-200 rounded-2xl p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2.5" class="flex-shrink-0 mt-0.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <p class="text-xs text-rose-700 leading-relaxed"><strong>Notifikasi telah dikirim ke kader untuk verifikasi lapangan.</strong> Status akan disebarluaskan ke warga setelah dikonfirmasi (human-in-the-loop).</p>
                </div>
                @endif

<div class="space-y-4">
    <!-- Kelembapan Tanah: Dangkal & Dalam -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Kelembapan Tanah Dangkal</h3>
                <span class="text-[9px] font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">PROBE 1</span>
            </div>
            <div class="flex items-baseline space-x-1">
                <p id="valMoisture" class="text-3xl font-light text-slate-800 font-mono">{{ number_format($current->soil_moisture ?? 0, 1) }}</p>
                <span class="text-sm font-semibold text-slate-400">%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                @php $mPct = min(100, max(0, $current->soil_moisture ?? 0)); @endphp
                <div id="valMoistureBar" class="h-1.5 rounded-full transition-all duration-300 {{ $mPct > 75 ? 'bg-rose-500' : ($mPct > 55 ? 'bg-yellow-500' : 'bg-blue-500') }}" style="width: {{ $mPct }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Kelembapan lapisan atas, indikator awal perubahan kondisi tanah</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
                <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Kelembapan Tanah Dalam</h3>
                <span class="text-[9px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">PROBE 2</span>
            </div>
            <div class="flex items-baseline space-x-1">
                <p id="valMoistureDeep" class="text-3xl font-light text-slate-800 font-mono">{{ number_format($current->moisture_percent_deep ?? 0, 1) }}</p>
                <span class="text-sm font-semibold text-slate-400">%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3">
                @php $mdPct = min(100, max(0, $current->moisture_percent_deep ?? 0)); @endphp
                <div id="valMoistureDeepBar" class="h-1.5 rounded-full transition-all duration-300 {{ $mdPct > 75 ? 'bg-rose-500' : ($mdPct > 55 ? 'bg-yellow-500' : 'bg-emerald-500') }}" style="width: {{ $mdPct }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Saturasi lapisan bawah, indikator kestabilan lereng</p>
        </div>
    </div>

    <!-- Accelerometer & Gyroscope -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-3 border-b border-slate-100 pb-2">Accelerometer (m/s²)</h3>
            <div id="valAccel" class="grid grid-cols-3 gap-2 text-center font-mono">
                <div class="bg-red-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-red-400 mb-1">X</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->accel_x ?? 0, 1) }}</span>
                </div>
                <div class="bg-amber-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-amber-400 mb-1">Y</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->accel_y ?? 0, 1) }}</span>
                </div>
                <div class="bg-emerald-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-emerald-400 mb-1">Z</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->accel_z ?? 0, 1) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
            <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-3 border-b border-slate-100 pb-2">Gyroscope (rad/s)</h3>
            <div id="valGyro" class="grid grid-cols-3 gap-2 text-center font-mono">
                <div class="bg-purple-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-purple-400 mb-1">X</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->gyro_x ?? 0, 1) }}</span>
                </div>
                <div class="bg-fuchsia-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-fuchsia-400 mb-1">Y</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->gyro_y ?? 0, 1) }}</span>
                </div>
                <div class="bg-blue-50 rounded-xl p-2">
                    <span class="block text-[9px] font-bold text-blue-400 mb-1">Z</span>
                    <span class="text-xs font-semibold text-slate-700 break-all leading-tight">{{ number_format($current->gyro_z ?? 0, 1) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rain Gauge (berdiri sendiri, full width) -->
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80">
        <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
            <h3 class="text-[11px] uppercase tracking-wider text-slate-400 font-bold flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><path d="M16 13v8M8 13v8M12 15v8M20 16.58A5 5 0 0018 7h-1.26A8 8 0 104 15.25"/></svg>
                Curah Hujan (Rain Gauge)
            </h3>
            <span class="text-[9px] font-bold text-sky-600 bg-sky-100 px-2 py-0.5 rounded-full">AKTIF</span>
        </div>
        <div class="flex items-end justify-between flex-wrap gap-3">
            <div class="flex items-end gap-2">
                <span id="valRainfall" class="text-4xl font-black text-sky-600 font-mono">{{ number_format($current->rainfall_mm ?? 0, 1) }}</span>
                <span class="text-sm text-slate-400 font-semibold mb-1">mm/jam</span>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-slate-400 uppercase tracking-wide font-bold">Kategori Intensitas</p>
                @php
                    $r = $current->rainfall_mm ?? 0;
                    $rainCat = $r > 50 ? ['Sangat Lebat', 'text-rose-600'] : ($r > 20 ? ['Lebat', 'text-orange-600'] : ($r > 5 ? ['Sedang', 'text-amber-600'] : ($r > 0 ? ['Ringan', 'text-sky-600'] : ['Tidak Ada Hujan', 'text-slate-400'])));
                @endphp
                <p id="valRainfallCategory" class="text-sm font-bold {{ $rainCat[1] }}">{{ $rainCat[0] }}</p>
            </div>
        </div>
        <p class="text-[10px] text-slate-400 mt-3">Intensitas curah hujan dari sensor tipping bucket</p>
    </div>
</div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80 text-center">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-2">Pitch</p>
                        <p id="valPitch" class="text-2xl font-bold font-mono text-slate-800">{{ number_format($current->pitch ?? 0, 2) }}<span class="text-sm text-slate-400">°</span></p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80 text-center">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-2">Roll</p>
                        <p id="valRoll" class="text-2xl font-bold font-mono text-slate-800">{{ number_format($current->roll ?? 0, 2) }}<span class="text-sm text-slate-400">°</span></p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200/80 text-center">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold mb-2">Vibrasi</p>
                        <p id="valVibration" class="text-2xl font-bold font-mono text-slate-800">{{ number_format($current->vibration ?? 0, 3) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Soil Moisture History (Dangkal)</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevMoist()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592;</button>
                            <span id="moistSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextMoist()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="moistureChart" height="100"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Soil Moisture History (Dalam)</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevMoistDeep()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592;</button>
                            <span id="moistDeepSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextMoistDeep()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="moistureDeepChart" height="100"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Accelerometer History</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevAccel()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592;</button>
                            <span id="accelSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextAccel()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="accelChart" height="100"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Landslide Status History</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="prevSlide()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">&#8592; Prev</button>
                            <span id="slideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextSlide()" class="text-xs px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-600 transition-colors">Next &#8594;</button>
                        </div>
                    </div>
                    <canvas id="statusChart" height="100"></canvas>
                </div>

                
                <div class="flex items-center gap-3 pt-2">
                    <div class="flex-1 h-px bg-gradient-to-r from-purple-200 to-transparent"></div>
                    <div class="flex items-center gap-2 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 px-4 py-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#008080" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        <span class="text-xs font-bold text-purple-700 uppercase tracking-widest">Predictive Analytics</span>
                    </div>
                    <div class="flex-1 h-px bg-gradient-to-l from-purple-200 to-transparent"></div>
                </div>

                <!-- Predictive Interval Toggle + Info -->
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-4 border border-purple-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-purple-900">Forecast Next Time Window</h3>
                            <p class="text-xs text-purple-600 mt-0.5">Proyeksi status prediktif berdasarkan model RF + LSTM per interval waktu</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-purple-500 uppercase">Interval:</span>
                            <button id="btnInterval1" onclick="setForecastInterval(1)" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-purple-600 text-white transition-all">1 Menit</button>
                            <button id="btnInterval5" onclick="setForecastInterval(5)" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-purple-200 text-purple-600 hover:bg-purple-50 transition-all">5 Menit</button>
                        </div>
                    </div>
                    <!-- Live forecast status pill -->
                    <div class="mt-3 flex flex-wrap gap-2" id="forecastStatusPills">
                        <div class="flex items-center gap-1.5 bg-white/70 rounded-full px-3 py-1 border border-purple-100">
                            <span class="text-[10px] font-bold text-purple-500">NEXT FORECAST:</span>
                            <span id="nextForecastStatus" class="text-[10px] font-black text-purple-800 font-mono">—</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/70 rounded-full px-3 py-1 border border-purple-100">
                            <span class="text-[10px] font-bold text-purple-500">RF CONF:</span>
                            <span id="nextRfConf" class="text-[10px] font-black text-indigo-700 font-mono">—</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-white/70 rounded-full px-3 py-1 border border-purple-100">
                            <span class="text-[10px] font-bold text-purple-500">LSTM CONF:</span>
                            <span id="nextLstmConf" class="text-[10px] font-black text-indigo-700 font-mono">—</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-purple-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-purple-500 font-bold">Forecast Status Timeline</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Keluaran Layer 1 — LSTM-Ensemble Forecasting · status prediksi next window vs status aktual</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="prevForecastSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8592;</button>
                            <span id="forecastSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextForecastSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="forecastStatusChart" height="110"></canvas>
                    <div class="flex flex-wrap gap-4 mt-3 text-[10px] font-semibold text-slate-500">
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-slate-300 opacity-60"></span> Aktual</span>
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-purple-500"></span> Forecast</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-purple-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-purple-500 font-bold">Model Confidence Score</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Layer 2 — RF Confidence &amp; LSTM Confidence per timestamp</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="prevConfSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8592;</button>
                            <span id="confSlideInfo" class="text-[10px] text-slate-400 font-mono min-w-[70px] text-center">Slide 1 / 1</span>
                            <button onclick="nextConfSlide()" class="text-xs px-2 py-1 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 transition-colors">&#8594;</button>
                        </div>
                    </div>
                    <canvas id="confidenceChart" height="110"></canvas>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-5 border border-purple-100">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-purple-500 font-bold">Projected Moisture Trend</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Proyeksi moisture next <span id="projectionWindowLabel">1 menit</span> ke depan (moving average)</p>
                        </div>
                    </div>
                    <canvas id="forecastMoistureChart" height="110"></canvas>
                    <div class="mt-3 flex items-center gap-2">
                        <span class="text-[10px] text-slate-400 font-bold uppercase">Proyeksi selanjutnya:</span>
                        <span id="projectedMoistureVal" class="font-mono text-sm font-black text-purple-700">—</span>
                        <span class="text-[10px] text-slate-400">%</span>
                    </div>
                </div>
                </div>{{-- /teknisMain --}}
            </div>

            <div class="lg:col-span-4 space-y-5">

                <div class="teknis-only bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Auto Refresh</h3>
                        <button id="refreshToggleBtn" onclick="toggleAutoRefresh()" class="px-3 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors">
                            ● Aktif
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">Data sensor diperbarui otomatis setiap <strong class="text-slate-600">10 detik</strong> dari endpoint <code class="font-mono text-blue-600 bg-blue-50 px-1 rounded">/api/sensor-now</code>.</p>
                    <div class="mt-3 text-xs text-slate-400">
                        Terakhir refresh: <span id="lastRefreshTime" class="font-mono text-slate-600">—</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                        <div class="flex items-center gap-2.5">
                            <!-- Logo BMKG Resmi via Wikimedia SVG -->
                            <img src="{{ asset('images/logo bmkg.png') }}" alt="BMKG Logo" class="w-7 h-9 object-contain shrink-0">
                            <div>
                                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 font-mono leading-none">Cuaca BMKG</h3>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Pacet, Mojokerto</p>
                            </div>
                        </div>
                        <span class="bg-sky-50 text-sky-600 text-[10px] font-bold px-2 py-0.5 rounded-md border border-sky-100 font-mono">
                            LIVE
                        </span>
                    </div>

                    <!-- Data Cuaca BMKG -->
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono pt-1">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Kondisi</p>
                                <p id="bmkgKondisi" class="font-bold text-slate-800">{{ $weatherInfo['condition'] ?? '–' }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Suhu</p>
                                <p id="bmkgSuhu" class="font-bold text-slate-800">{{ ($weatherInfo['temp'] ?? null) !== null ? $weatherInfo['temp'] . '°C' : '–' }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Kelembapan udara</p>
                                <p id="bmkgRh" class="font-bold text-slate-800">{{ ($weatherInfo['humidity'] ?? null) !== null ? $weatherInfo['humidity'] . '%' : '–' }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="text-base"></span>
                            <div>
                                <p class="text-[10px] text-slate-400 font-sans">Perkiraan hujan</p>
                                <p id="bmkgHujan" class="font-bold text-slate-800">{{ ($weatherInfo['rainfall'] ?? null) !== null ? $weatherInfo['rainfall'] . ' mm/jam' : '–' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                        <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Tanya LARAS</h3>
                            <p class="text-[10px] text-slate-400">Asisten siaga longsor · menjawab dari data sensor &amp; BMKG terkini</p>
                        </div>
                    </div>
                    <div id="chatLog" class="h-52 overflow-y-auto p-4 space-y-3 text-xs">
                        <div class="flex gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-[9px] font-bold text-indigo-600">L</span>
                            </div>
                            <div class="bg-slate-50 rounded-xl px-3 py-2 text-slate-600 max-w-[85%]">
                                Halo, saya LARAS. Saya bisa menjelaskan kondisi lereng saat ini, cuaca, posko evakuasi terdekat, dan apa yang perlu disiapkan. Silakan bertanya.
                            </div>
                        </div>
                    </div>
                    <div class="warga-only px-3 pt-3 flex flex-wrap gap-2 border-t border-slate-100">
                        <button type="button" onclick="askQuick('Apakah lereng aman saat ini?')" class="px-3 py-1.5 rounded-full bg-air-100 border border-air-300 text-[12px] font-semibold text-teal-700 hover:bg-air-200">Apakah aman saat ini?</button>
                        <button type="button" onclick="askQuick('Ke mana saya harus mengungsi jika terjadi bahaya?')" class="px-3 py-1.5 rounded-full bg-air-100 border border-air-300 text-[12px] font-semibold text-teal-700 hover:bg-air-200">Ke mana harus mengungsi?</button>
                        <button type="button" onclick="askQuick('Apa saja tanda-tanda akan terjadi longsor?')" class="px-3 py-1.5 rounded-full bg-air-100 border border-air-300 text-[12px] font-semibold text-teal-700 hover:bg-air-200">Tanda-tanda longsor</button>
                        <button type="button" onclick="askQuick('Apa yang harus disiapkan di tas siaga bencana?')" class="px-3 py-1.5 rounded-full bg-air-100 border border-air-300 text-[12px] font-semibold text-teal-700 hover:bg-air-200">Isi tas siaga</button>
                    </div>
                    <div class="border-t border-slate-100 p-3 flex gap-2">
                        <input id="chatInput" type="text" placeholder="Tulis pertanyaan Anda…" onkeydown="if(event.key==='Enter'){event.preventDefault();sendChat();}" class="flex-1 text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 text-slate-700">
                        <button onclick="sendChat()" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="warga-only card p-5">
                    <p class="text-[15px] font-bold text-deep-900 flex items-center gap-2"><span class="material-symbols-outlined text-[20px] text-rose-600">emergency</span>Nomor darurat</p>
                    <a href="tel:112" class="mt-3 flex items-center justify-between gap-3 p-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white transition">
                        <span><span class="block font-mono text-[22px] font-bold leading-none">112</span><span class="block text-[12px] text-white/85 mt-1">Darurat, gratis</span></span>
                        <span class="material-symbols-outlined text-[26px]">call</span>
                    </a>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <a href="tel:117" class="p-3 rounded-xl bg-air-100 border border-air-300 hover:bg-air-200"><span class="block font-mono font-bold text-teal-700 text-[17px]">117</span><span class="block text-[12px] text-deep-600">BNPB</span></a>
                        <a href="tel:115" class="p-3 rounded-xl bg-air-100 border border-air-300 hover:bg-air-200"><span class="block font-mono font-bold text-teal-700 text-[17px]">115</span><span class="block text-[12px] text-deep-600">Basarnas (SAR)</span></a>
                    </div>
                    <a href="{{ route('peta-evakuasi.index') }}" class="mt-3 inline-flex items-center gap-1 text-[14px] font-semibold text-teal-700 hover:underline">Lihat posko evakuasi terdekat <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
                </div>

                <div class="teknis-only bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-bold">Riwayat Log ({{ count($logs) }} entri)</h3>
                    </div>
                    <div class="overflow-x-auto max-h-72 overflow-y-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 sticky top-0">
                                <tr>
                                    <th class="text-left px-4 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Waktu</th>
                                    <th class="text-right px-3 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Moist%</th>
                                    <th class="text-right px-3 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Vibr</th>
                                    <th class="text-center px-3 py-2.5 text-slate-400 font-bold uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($logs->sortByDesc('timestamp')->take(50) as $log)
                                @php
                                    $ls = strtoupper($log->status_ai ?? 'UNKNOWN');
                                    $lsBg = $ls === 'BAHAYA' ? 'bg-rose-100 text-rose-700' : ($ls === 'WASPADA' ? 'bg-yellow-100 text-yellow-700' : 'bg-emerald-100 text-emerald-700');
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-2.5 font-mono text-slate-500 text-[10px]">
                                        {{ \Carbon\Carbon::parse($log->timestamp)->format('d M H:i') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right font-mono text-slate-700">{{ number_format($log->soil_moisture ?? 0, 1) }}</td>
                                    <td class="px-3 py-2.5 text-right font-mono text-slate-700">{{ number_format($log->vibration ?? 0, 3) }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $lsBg }}">{{ $ls }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data log.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        

            </div>
        </div>
    </main>
</div>

<script>
Chart.defaults.font.family = 'Inter, ui-sans-serif, sans-serif';
Chart.defaults.color = '#64748b';

const PAGE = 10;

function enterDashboard() {
    // simpan #live di URL supaya refresh / link dari halaman lain langsung ke Live Control
    if (location.hash !== '#live') history.replaceState(null, '', '#live');
    if (window.larasSetNav) larasSetNav('live');
    window.scrollTo({ top: 0 });
    const lp = document.getElementById('landing-page');
    const dp = document.getElementById('dashboard-page');
    lp.style.opacity = '0';
    lp.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        lp.style.display = 'none';
        dp.style.display = 'block';
        dp.style.opacity = '0';
        dp.style.transform = 'translateY(10px)';
        setTimeout(() => {
            dp.style.opacity = '1';
            dp.style.transform = 'translateY(0)';
            initCharts();
            startAutoRefresh();
            startBmkgRefresh();
        }, 50);
    }, 350);
}

function backToLanding() {
    if (location.hash === '#live') history.replaceState(null, '', location.pathname);
    if (window.larasSetNav) larasSetNav('home');
    const lp = document.getElementById('landing-page');
    const dp = document.getElementById('dashboard-page');
    dp.style.opacity = '0';
    dp.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        dp.style.display = 'none';
        lp.style.display = 'block';
        lp.style.opacity = '0';
        lp.style.transform = 'translateY(10px)';
        setTimeout(() => {
            lp.style.opacity = '1';
            lp.style.transform = 'translateY(0)';
            if (window.larasHomeMap) window.larasHomeMap.invalidateSize();
        }, 50);
    }, 350);
}

function goToLandingSection(sectionId) {
    backToLanding();
    setTimeout(() => {
        const el = document.getElementById(sectionId);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 450);
}

function fmtLabel(ts) {
    if (!ts) return 'N/A';
    const d = new Date(ts);
    if (isNaN(d.getTime())) return ts;
    const day = String(d.getDate()).padStart(2, '0');
    const mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()];
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    return day + ' ' + mon + ' ' + hh + ':' + mm;
}

let moistSlide = 0, moistDeepSlide = 0, accelSlide = 0, currentSlide = 0;
let moistureChart = null, moistureDeepChart = null, accelChart = null, statusChart = null;
let allStatusData = [];

function statusToNumber(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN') return 1;
    if (su === 'WASPADA') return 2;
    if (su === 'BAHAYA') return 3;
    return 0;
}

function getStatusColor(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN') return { bg: 'rgba(34,197,94,0.25)', border: '#22c55e' };
    if (su === 'WASPADA') return { bg: 'rgba(250,180,75,0.25)', border: '#FAB44B' };
    if (su === 'BAHAYA') return { bg: 'rgba(244,63,94,0.25)', border: '#f43f5e' };
    return { bg: 'rgba(148,163,184,0.2)', border: '#94a3b8' };
}

function renderMoistureSlide() {
    const total = Math.max(1, Math.ceil(phpLogs.length / PAGE));
    const slice = phpLogs.slice(moistSlide * PAGE, (moistSlide + 1) * PAGE);
    document.getElementById('moistSlideInfo').textContent = 'Slide ' + (moistSlide + 1) + ' / ' + total;
    if (moistureChart) moistureChart.destroy();
    moistureChart = new Chart(document.getElementById('moistureChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [{
                label: 'Moisture %',
                data: slice.map(d => d.soil_moisture),
                borderColor: '#008080',
                backgroundColor: 'rgba(0,128,128,0.08)',
                fill: true, tension: 0.4, pointRadius: 3, borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100, grid: { color: '#f1f5f9' } }, x: { grid: { color: '#f1f5f9' } } }
        }
    });
}

function prevMoist() { if (moistSlide > 0) { moistSlide--; renderMoistureSlide(); } }
function nextMoist() { if (moistSlide < Math.ceil(phpLogs.length / PAGE) - 1) { moistSlide++; renderMoistureSlide(); } }
function renderMoistureDeepSlide() {
    const total = Math.max(1, Math.ceil(phpLogs.length / PAGE));
    const slice = phpLogs.slice(moistDeepSlide * PAGE, (moistDeepSlide + 1) * PAGE);
    document.getElementById('moistDeepSlideInfo').textContent = 'Slide ' + (moistDeepSlide + 1) + ' / ' + total;
    if (moistureDeepChart) moistureDeepChart.destroy();
    moistureDeepChart = new Chart(document.getElementById('moistureDeepChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [{
                label: 'Moisture Dalam %',
                data: slice.map(d => d.moisture_percent_deep),
                borderColor: '#133332',
                backgroundColor: 'rgba(19,51,50,0.08)',
                fill: true, tension: 0.4, pointRadius: 3, borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { min: 0, max: 100, grid: { color: '#f1f5f9' } }, x: { grid: { color: '#f1f5f9' } } }
        }
    });
}

function prevMoistDeep() { if (moistDeepSlide > 0) { moistDeepSlide--; renderMoistureDeepSlide(); } }
function nextMoistDeep() { if (moistDeepSlide < Math.ceil(phpLogs.length / PAGE) - 1) { moistDeepSlide++; renderMoistureDeepSlide(); } }

function renderAccelSlide() {
    const total = Math.max(1, Math.ceil(phpLogs.length / PAGE));
    const slice = phpLogs.slice(accelSlide * PAGE, (accelSlide + 1) * PAGE);
    document.getElementById('accelSlideInfo').textContent = 'Slide ' + (accelSlide + 1) + ' / ' + total;
    if (accelChart) accelChart.destroy();
    accelChart = new Chart(document.getElementById('accelChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [
                { label: 'X', data: slice.map(d => d.accel_x), borderColor: '#133332', tension: 0.4, pointRadius: 2, borderWidth: 1.5 },
                { label: 'Y', data: slice.map(d => d.accel_y), borderColor: '#008080', tension: 0.4, pointRadius: 2, borderWidth: 1.5 },
                { label: 'Z', data: slice.map(d => d.accel_z), borderColor: '#D68F14', tension: 0.4, pointRadius: 2, borderWidth: 1.5 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } },
            scales: { x: { grid: { color: '#f1f5f9' } }, y: { grid: { color: '#f1f5f9' } } }
        }
    });
}

function prevAccel() { if (accelSlide > 0) { accelSlide--; renderAccelSlide(); } }
function nextAccel() { if (accelSlide < Math.ceil(phpLogs.length / PAGE) - 1) { accelSlide++; renderAccelSlide(); } }

function renderSlide() {
    const total = Math.max(1, Math.ceil(allStatusData.length / PAGE));
    const slice = allStatusData.slice(currentSlide * PAGE, (currentSlide + 1) * PAGE);
    document.getElementById('slideInfo').textContent = 'Slide ' + (currentSlide + 1) + ' / ' + total;
    if (statusChart) statusChart.destroy();
    // Grafik garis bertangga: status adalah tingkat (AMAN/WASPADA/BAHAYA), jadi garis
    // naik/turun tegak saat status berubah. Warna titik & ruas garis mengikuti status.
    const stStatus = slice.map(d => d.status_ai ?? d.status);
    const stColor  = i => getStatusColor(stStatus[i]).border;
    statusChart = new Chart(document.getElementById('statusChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [{
                label: 'Status Longsor',
                data: stStatus.map(statusToNumber),
                stepped: 'before',
                fill: false,
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: stStatus.map(s => getStatusColor(s).border),
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
                segment: { borderColor: ctx => stColor(ctx.p1DataIndex) }
            }]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' Status: ' + ((stStatus[ctx.dataIndex] || 'UNKNOWN').toUpperCase()) } }
            },
            scales: {
                y: {
                    min: 0.5, max: 3.5, grid: { color: '#f1f5f9' },
                    ticks: { stepSize: 1, callback: v => { if(v==1) return 'AMAN'; if(v==2) return 'WASPADA'; if(v==3) return 'BAHAYA'; return ''; } }
                },
                x: { grid: { color: '#f1f5f9' } }
            }
        }
    });
}

function prevSlide() { if (currentSlide > 0) { currentSlide--; renderSlide(); } }
function nextSlide() { if (currentSlide < Math.ceil(allStatusData.length / PAGE) - 1) { currentSlide++; renderSlide(); } }

function initCharts() {
    moistSlide = Math.max(0, Math.ceil(phpLogs.length / PAGE) - 1);
    moistDeepSlide = Math.max(0, Math.ceil(phpLogs.length / PAGE) - 1);
    accelSlide = moistSlide;
    renderMoistureSlide();
    renderMoistureDeepSlide();
    renderAccelSlide();

    fetch('/api/status-history')
        .then(r => r.json())
        .then(data => {
            allStatusData = data;
            currentSlide = Math.max(0, Math.ceil(data.length / PAGE) - 1);
            renderSlide();
        })
        .catch(() => {
            allStatusData = phpLogs.map(l => ({ timestamp: l.timestamp, status: l.status_ai }));
            currentSlide = Math.max(0, Math.ceil(allStatusData.length / PAGE) - 1);
            renderSlide();
        });
    loadForecastData();
}

let autoRefreshEnabled = true;
let autoRefreshInterval = null;

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const btn = document.getElementById('refreshToggleBtn');
    if (autoRefreshEnabled) {
        btn.textContent = '● Aktif';
        btn.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors';
        startAutoRefresh();
    } else {
        btn.textContent = '○ Nonaktif';
        btn.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors';
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    }
}

function updateDOMWithLatest(d) {
    if (!d) return;
    const status = (d.status_ai || '').toString().toUpperCase();

    const card = document.getElementById('statusCard');
    const txt = document.getElementById('statusText');
    card.className = 'transition-all duration-300 rounded-2xl shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between border-l-4 gap-4 ';
    if (status === 'BAHAYA') {
        card.className += 'bg-rose-50 border-rose-500';
        txt.className = 'text-3xl font-extrabold tracking-tight text-rose-700';
        txt.textContent = 'BAHAYA : EVAKUASI SEGERA';
    } else if (status === 'WASPADA') {
        card.className += 'bg-yellow-50 border-yellow-500';
        txt.className = 'text-3xl font-extrabold tracking-tight text-yellow-700';
        txt.textContent = 'WASPADA : SIAGA PENUH';
    } else {
        card.className += 'bg-white border-emerald-500';
        txt.className = 'text-3xl font-extrabold tracking-tight text-emerald-700';
        txt.textContent = 'SISTEM AMAN';
    }

    const setEl = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
    setEl('valMoisture', parseFloat(d.soil_moisture || 0).toFixed(1));
    setEl('valMoistureDeep', parseFloat(d.moisture_percent_deep || 0).toFixed(1));
    setEl('valRainfall', parseFloat(d.rainfall_mm || 0).toFixed(1));
    setEl('valPitch', parseFloat(d.pitch || 0).toFixed(2));
    setEl('valRoll', parseFloat(d.roll || 0).toFixed(2));
    setEl('valVibration', parseFloat(d.vibration || 0).toFixed(3));
    setEl('lastUpdate', d.timestamp || '—');

    const moistureBar = document.getElementById('valMoistureBar');
    if (moistureBar) {
        const mp = Math.min(100, Math.max(0, parseFloat(d.soil_moisture || 0)));
        moistureBar.style.width = mp + '%';
        moistureBar.className = 'h-1.5 rounded-full transition-all duration-300 ' + (mp > 75 ? 'bg-rose-500' : (mp > 55 ? 'bg-yellow-500' : 'bg-blue-500'));
    }
    const moistureDeepBar = document.getElementById('valMoistureDeepBar');
    if (moistureDeepBar) {
        const mdp = Math.min(100, Math.max(0, parseFloat(d.moisture_percent_deep || 0)));
        moistureDeepBar.style.width = mdp + '%';
        moistureDeepBar.className = 'h-1.5 rounded-full transition-all duration-300 ' + (mdp > 75 ? 'bg-rose-500' : (mdp > 55 ? 'bg-yellow-500' : 'bg-emerald-500'));
    }
    const rainCat = document.getElementById('valRainfallCategory');
    if (rainCat) {
        const r = parseFloat(d.rainfall_mm || 0);
        let cat = 'Tidak Ada Hujan', color = 'text-slate-400';
        if (r > 50) { cat = 'Sangat Lebat'; color = 'text-rose-600'; }
        else if (r > 20) { cat = 'Lebat'; color = 'text-orange-600'; }
        else if (r > 5) { cat = 'Sedang'; color = 'text-amber-600'; }
        else if (r > 0) { cat = 'Ringan'; color = 'text-sky-600'; }
        rainCat.textContent = cat;
        rainCat.className = 'text-sm font-bold ' + color;
    }

    const now = new Date();
    const timeStr = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0') + ':' + now.getSeconds().toString().padStart(2,'0');
    setEl('lastRefreshTime', timeStr);
    if (typeof updateWarga === 'function') updateWarga(d);
}

function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => {
        if (!autoRefreshEnabled) return;
        fetch('/api/sensor-now')
            .then(r => r.json())
            .then(d => updateDOMWithLatest(d))
            .catch(() => {});
    }, 10000);
}

let bmkgRefreshInterval = null;

function updateBmkgDisplay(w) {
    if (!w) return;
    const put = (id, v) => { const e = document.getElementById(id); if (e && v !== undefined && v !== null && v !== '') e.textContent = v; };
    put('bmkgKondisi', w.condition);
    put('bmkgSuhu', w.temp != null ? w.temp + '°C' : null);
    put('bmkgRh', w.humidity != null ? w.humidity + '%' : null);
    put('bmkgHujan', w.rainfall != null ? w.rainfall + ' mm/jam' : null);
    const el = document.getElementById('bmkg-display');
    if (!el) return;
    let text = '' + (w.condition || 'N/A');
    if (w.temp)     text += ' · ' + w.temp + '°C';
    if (w.humidity) text += ' · RH ' + w.humidity + '%';
    if (w.rainfall && parseFloat(w.rainfall) > 0) text += ' · ' + w.rainfall + ' mm/jam';
    el.textContent = text;
}

function fetchBmkg() {
    fetch('/api/weather-bmkg')
        .then(r => r.json())
        .then(w => updateBmkgDisplay(w))
        .catch(() => {});
}

function startBmkgRefresh() {
    if (bmkgRefreshInterval) clearInterval(bmkgRefreshInterval);
    fetchBmkg(); // langsung fetch pertama kali
    bmkgRefreshInterval = setInterval(fetchBmkg, 5 * 60 * 1000); // refresh tiap 5 menit
}

function appendChat (role, text) {
    const log = document.getElementById('chatLog');
    const div = document.createElement('div');
    div.className = 'flex gap-2 ' + (role === 'user' ? 'flex-row-reverse' : '');
    const avatar = document.createElement('div');
    avatar.className = 'min-w-6 px-1 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-[9px] font-bold ' + (role === 'user' ? 'bg-blue-100 text-blue-600' : 'bg-indigo-100 text-indigo-600');
    avatar.textContent = role === 'user' ? 'Anda' : 'L';
    const bubble = document.createElement('div');
    bubble.className = 'rounded-xl px-3 py-2 text-slate-700 max-w-[85%] text-[13px] leading-relaxed ' + (role === 'user' ? 'bg-blue-50 text-blue-800' : 'bg-slate-50');
    bubble.style.whiteSpace = 'pre-line';
    bubble.innerText = text;
    div.appendChild(avatar);
    div.appendChild(bubble);
    log.appendChild(div);
    log.scrollTop = log.scrollHeight;
}

const chatHistory = [];
let chatBusy = false;

function sendChat() {
    const input = document.getElementById('chatInput');
    const msg = input.value.trim();
    if (!msg || chatBusy) return;
    input.value = '';
    appendChat('user', msg);
    chatBusy = true;

    // indikator "LARAS sedang mengetik"
    const log = document.getElementById('chatLog');
    const typing = document.createElement('div');
    typing.id = 'typing-indicator';
    typing.className = 'flex gap-2';
    typing.innerHTML = '<div class="min-w-6 px-1 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-[9px] font-bold bg-indigo-100 text-indigo-600">L</div>'
        + '<div class="rounded-xl px-3 py-2 bg-slate-50 text-slate-500 text-[13px]">LARAS sedang menyiapkan jawaban…</div>';
    log.appendChild(typing);
    log.scrollTop = log.scrollHeight;

    const mode = document.getElementById('teknisMain') && !document.getElementById('teknisMain').classList.contains('hidden') ? 'teknis' : 'warga';

    fetch('/api/chatbot', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message: msg, mode, history: chatHistory.slice(-8) })
    })
    .then(async r => {
        const d = await r.json().catch(() => ({}));
        if (r.status === 429) throw new Error('Terlalu banyak pertanyaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.');
        if (r.status === 422) throw new Error('Pertanyaan terlalu panjang. Coba persingkat (maksimal 500 karakter).');
        if (!r.ok) throw new Error('LARAS sedang tidak bisa dihubungi. Untuk keadaan darurat, hubungi 112.');
        return d;
    })
    .then(d => {
        document.getElementById('typing-indicator')?.remove();
        const reply = d.reply || 'Maaf, LARAS belum bisa menjawab saat ini.';
        appendChat('ai', reply);
        chatHistory.push({ role: 'user', text: msg }, { role: 'ai', text: reply });
        while (chatHistory.length > 10) chatHistory.shift();
    })
    .catch(err => {
        document.getElementById('typing-indicator')?.remove();
        appendChat('ai', err.message || 'LARAS sedang tidak bisa dihubungi. Untuk keadaan darurat, hubungi 112.');
    })
    .finally(() => { chatBusy = false; });
}

document.getElementById('chatInput')?.addEventListener('keydown', e => { if (e.key === 'Enter') sendChat(); });


let forecastInterval = 1;
let allForecastData  = [];
let forecastSlide    = 0;
let confSlide        = 0;
let forecastStatusChart  = null;
let confidenceChart      = null;
let forecastMoistureChart = null;

function setForecastInterval(min) {
    forecastInterval = min;
    document.getElementById('btnInterval1').className = min === 1
        ? 'px-3 py-1.5 text-xs font-bold rounded-lg bg-purple-600 text-white transition-all'
        : 'px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-purple-200 text-purple-600 hover:bg-purple-50 transition-all';
    document.getElementById('btnInterval5').className = min === 5
        ? 'px-3 py-1.5 text-xs font-bold rounded-lg bg-purple-600 text-white transition-all'
        : 'px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-purple-200 text-purple-600 hover:bg-purple-50 transition-all';
    const lbl = document.getElementById('projectionWindowLabel');
    if (lbl) lbl.textContent = min + ' menit';
    renderForecastCharts();
}

function statusToNumForecast(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN')    return 1;
    if (su === 'WASPADA') return 2;
    if (su === 'BAHAYA')  return 3;
    return 0;
}

function forecastStatusColor(s) {
    const su = (s || '').toUpperCase();
    if (su === 'AMAN')    return { bg: 'rgba(34,197,94,0.35)',  border: '#16a34a' };
    if (su === 'WASPADA') return { bg: 'rgba(250,180,75,0.55)', border: '#FAB44B' };
    if (su === 'BAHAYA')  return { bg: 'rgba(239,68,68,0.6)',   border: '#ef4444' };
    return { bg: 'rgba(148,163,184,0.3)', border: '#94a3b8' };
}

function sampleByInterval(data, intervalMin) {
    if (!data.length) return data;
    if (data.length < 2) return data;
    const t1 = new Date(data[0].timestamp).getTime();
    const t2 = new Date(data[1].timestamp).getTime();
    const diffMs = Math.abs(t2 - t1);
    const diffMin = diffMs / 60000;
    const step = Math.max(1, Math.round(intervalMin / (diffMin || 1)));
    return data.filter((_, i) => i % step === 0);
}

function generateProjectedTimestamps(lastTs, count, intervalMin) {
    const result = [];
    let base = new Date(lastTs).getTime();
    for (let i = 1; i <= count; i++) {
        base += intervalMin * 60 * 1000;
        const d = new Date(base);
        const day = String(d.getDate()).padStart(2,'0');
        const mon = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()];
        const hh  = String(d.getHours()).padStart(2,'0');
        const mm  = String(d.getMinutes()).padStart(2,'0');
        result.push(day + ' ' + mon + ' ' + hh + ':' + mm);
    }
    return result;
}

function projectMoisture(data, steps, intervalMin) {
    if (!data.length) return [];
    const sampled = sampleByInterval(data, intervalMin);
    const window  = Math.min(5, sampled.length);
    const last5   = sampled.slice(-window).map(d => parseFloat(d.moisture_percent || 0));
    const avg     = last5.reduce((a, b) => a + b, 0) / last5.length;
    const trend   = last5.length >= 2 ? (last5[last5.length-1] - last5[0]) / last5.length * 0.3 : 0;
    const result  = [];
    for (let i = 1; i <= steps; i++) {
        result.push(Math.min(100, Math.max(0, avg + trend * i)));
    }
    return result;
}

const FORECAST_PAGE = 10;

function renderForecastStatusChart(data) {
    const sampled = sampleByInterval(data, forecastInterval);
    const total   = Math.max(1, Math.ceil(sampled.length / FORECAST_PAGE));
    forecastSlide = Math.min(forecastSlide, total - 1);
    const slice   = sampled.slice(forecastSlide * FORECAST_PAGE, (forecastSlide + 1) * FORECAST_PAGE);
    document.getElementById('forecastSlideInfo').textContent = 'Slide ' + (forecastSlide + 1) + ' / ' + total;

    if (forecastStatusChart) forecastStatusChart.destroy();
    forecastStatusChart = new Chart(document.getElementById('forecastStatusChart'), {
        type: 'bar',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [
                {
                    label: 'Status Aktual',
                    data: slice.map(d => statusToNumForecast(d.status)),
                    backgroundColor: 'rgba(148,163,184,0.35)',
                    borderColor: '#94a3b8',
                    borderWidth: 1.5,
                    borderRadius: 3,
                    barPercentage: 0.9,
                    categoryPercentage: 0.55,
                },
                {
                    label: 'Forecast Status',
                    data: slice.map(d => statusToNumForecast(d.forecast_status)),
                    backgroundColor: slice.map(d => forecastStatusColor(d.forecast_status).bg),
                    borderColor:     slice.map(d => forecastStatusColor(d.forecast_status).border),
                    borderWidth: 1.5,
                    borderRadius: 3,
                    barPercentage: 0.9,
                    categoryPercentage: 0.55,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const v = ctx.raw;
                            const label = v === 1 ? 'AMAN' : v === 2 ? 'WASPADA' : v === 3 ? 'BAHAYA' : 'N/A';
                            return ctx.dataset.label + ': ' + label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0, max: 3.5, grid: { color: '#eef1f3' },
                    ticks: { stepSize: 1, callback: v => { if(v==1) return 'AMAN'; if(v==2) return 'WASPADA'; if(v==3) return 'BAHAYA'; return ''; }, color: '#133332' }
                },
                x: { grid: { color: '#eef1f3' } }
            }
        }
    });
}

function renderConfidenceChart(data) {
    const sampled = sampleByInterval(data, forecastInterval);
    const total   = Math.max(1, Math.ceil(sampled.length / FORECAST_PAGE));
    confSlide     = Math.min(confSlide, total - 1);
    const slice   = sampled.slice(confSlide * FORECAST_PAGE, (confSlide + 1) * FORECAST_PAGE);
    document.getElementById('confSlideInfo').textContent = 'Slide ' + (confSlide + 1) + ' / ' + total;

    if (confidenceChart) confidenceChart.destroy();
    confidenceChart = new Chart(document.getElementById('confidenceChart'), {
        type: 'line',
        data: {
            labels: slice.map(d => fmtLabel(d.timestamp)),
            datasets: [
                {
                    label: 'RF Confidence',
                    data: slice.map(d => d.rf_confidence != null ? parseFloat(d.rf_confidence) : null),
                    borderColor: '#008080',
                    backgroundColor: 'rgba(0,128,128,0.08)',
                    fill: false, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    spanGaps: true
                },
                {
                    label: 'LSTM Confidence',
                    data: slice.map(d => d.lstm_confidence != null ? parseFloat(d.lstm_confidence) : null),
                    borderColor: '#133332',
                    backgroundColor: 'rgba(152,171,203,0.08)',
                    fill: false, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    spanGaps: true
                },
                {
                    label: 'Forecast Confidence',
                    data: slice.map(d => d.forecast_confidence != null ? parseFloat(d.forecast_confidence) : null),
                    borderColor: '#D68F14',
                    backgroundColor: 'rgba(152,171,203,0.08)',
                    fill: false, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    borderDash: [4, 3],
                    spanGaps: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': ' + (ctx.raw != null ? ctx.raw.toFixed(1) + '%' : 'N/A')
                    }
                }
            },
            scales: {
                y: { min: 0, max: 100, grid: { color: '#eef1f3' }, ticks: { callback: v => v + '%', color: '#133332' } },
                x: { grid: { color: '#eef1f3' } }
            }
        }
    });
}

function renderForecastMoistureChart(data) {
    const sampled     = sampleByInterval(data, forecastInterval);
    const histSlice   = sampled.slice(-FORECAST_PAGE); // last N historical points
    const projSteps   = 5;
    const projValues  = projectMoisture(data, projSteps, forecastInterval);
    const lastTs      = histSlice.length ? histSlice[histSlice.length - 1].timestamp : new Date().toISOString();
    const projLabels  = generateProjectedTimestamps(lastTs, projSteps, forecastInterval);

    const histLabels  = histSlice.map(d => fmtLabel(d.timestamp));
    const histValues  = histSlice.map(d => parseFloat(d.moisture_percent || 0));

    const allLabels   = [...histLabels, ...projLabels];
    const histDataset = [...histValues, ...new Array(projSteps).fill(null)];
    const projDataset = [...new Array(histLabels.length - 1).fill(null), histValues[histValues.length - 1] || null, ...projValues];

    if (projValues.length) {
        document.getElementById('projectedMoistureVal').textContent = projValues[0].toFixed(1);
    }

    if (forecastMoistureChart) forecastMoistureChart.destroy();
    forecastMoistureChart = new Chart(document.getElementById('forecastMoistureChart'), {
        type: 'line',
        data: {
            labels: allLabels,
            datasets: [
                {
                    label: 'Moisture Aktual (%)',
                    data: histDataset,
                    borderColor: '#008080',
                    backgroundColor: 'rgba(0,128,128,0.08)',
                    fill: true, tension: 0.4, pointRadius: 3, borderWidth: 2,
                    spanGaps: false
                },
                {
                    label: 'Proyeksi +' + forecastInterval + 'min',
                    data: projDataset,
                    borderColor: '#D68F14',
                    backgroundColor: 'rgba(152,171,203,0.12)',
                    fill: true, tension: 0.4, pointRadius: 4, borderWidth: 2,
                    borderDash: [5, 4],
                    pointStyle: 'triangle',
                    spanGaps: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } },
                annotation: {}
            },
            scales: {
                y: { min: 0, max: 100, grid: { color: '#eef1f3' }, ticks: { callback: v => v + '%', color: '#133332' } },
                x: { grid: { color: '#eef1f3' } }
            }
        }
    });
}

function updateForecastPills(data) {
    if (!data.length) return;
    const latest = data[data.length - 1];
    const el = document.getElementById('nextForecastStatus');
    const rfEl = document.getElementById('nextRfConf');
    const lstmEl = document.getElementById('nextLstmConf');
    if (el) {
        el.textContent = latest.forecast_status || '—';
        const s = (latest.forecast_status || '').toUpperCase();
        el.className = 'text-[10px] font-black font-mono ' + (s === 'BAHAYA' ? 'text-rose-700' : s === 'WASPADA' ? 'text-yellow-700' : 'text-emerald-700');
    }
    if (rfEl)   rfEl.textContent   = latest.rf_confidence   != null ? parseFloat(latest.rf_confidence).toFixed(1)   + '%' : '—';
    if (lstmEl) lstmEl.textContent = latest.lstm_confidence != null ? parseFloat(latest.lstm_confidence).toFixed(1) + '%' : '—';
    if (typeof updateWargaForecast === 'function') updateWargaForecast(latest);
}

function renderForecastCharts() {
    if (!allForecastData.length) return;
    forecastSlide = Math.max(0, Math.ceil(sampleByInterval(allForecastData, forecastInterval).length / FORECAST_PAGE) - 1);
    confSlide     = forecastSlide;
    updateForecastPills(allForecastData);
    renderForecastStatusChart(allForecastData);
    renderConfidenceChart(allForecastData);
    renderForecastMoistureChart(allForecastData);
}

function prevForecastSlide() { if (forecastSlide > 0) { forecastSlide--; renderForecastStatusChart(allForecastData); } }
function nextForecastSlide() {
    const max = Math.ceil(sampleByInterval(allForecastData, forecastInterval).length / FORECAST_PAGE) - 1;
    if (forecastSlide < max) { forecastSlide++; renderForecastStatusChart(allForecastData); }
}
function prevConfSlide() { if (confSlide > 0) { confSlide--; renderConfidenceChart(allForecastData); } }
function nextConfSlide() {
    const max = Math.ceil(sampleByInterval(allForecastData, forecastInterval).length / FORECAST_PAGE) - 1;
    if (confSlide < max) { confSlide++; renderConfidenceChart(allForecastData); }
}

function loadForecastData() {
    fetch('/api/forecast-history')
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) throw new Error('empty');
            allForecastData = data.map(d => ({
                timestamp:           d.timestamp,
                status:              (d.status || 'AMAN').toString().toUpperCase(),
                forecast_status:     (d.forecast_status || d.status || 'AMAN').toString().toUpperCase(),
                forecast_confidence: d.forecast_confidence != null ? parseFloat(d.forecast_confidence) : null,
                rf_confidence:       d.rf_confidence       != null ? parseFloat(d.rf_confidence)       : null,
                lstm_confidence:     d.lstm_confidence     != null ? parseFloat(d.lstm_confidence)     : null,
                moisture_percent:    parseFloat(d.moisture_percent || 0),
                vibration:           parseFloat(d.vibration || 0),
                pitch:               parseFloat(d.pitch || 0),
            }));
            renderForecastCharts();
        })
        .catch(() => {
            allForecastData = phpLogs.map(l => ({
                timestamp:           l.timestamp,
                status:              (l.status || l.status_ai || 'AMAN').toString().toUpperCase(),
                forecast_status:     (l.forecast_status || l.status || l.status_ai || 'AMAN').toString().toUpperCase(),
                forecast_confidence: l.forecast_confidence != null ? parseFloat(l.forecast_confidence) : null,
                rf_confidence:       l.rf_confidence       != null ? parseFloat(l.rf_confidence)       : null,
                lstm_confidence:     l.lstm_confidence     != null ? parseFloat(l.lstm_confidence)     : null,
                moisture_percent:    parseFloat(l.moisture_percent || l.soil_moisture || 0),
                vibration:           parseFloat(l.vibration || 0),
                pitch:               parseFloat(l.pitch || 0),
            }));
            renderForecastCharts();
        });
}




// =====================================================================
// MODE WARGA: menerjemahkan data sensor ke bahasa sehari-hari
// =====================================================================
const W_STATUS = {
    AMAN: {
        title: 'Aman', icon: 'verified_user',
        card: 'bg-emerald-50 border-emerald-300', iconBox: 'bg-emerald-500 text-white', titleColor: 'text-emerald-800',
        desc: 'Kondisi lereng saat ini aman. Silakan beraktivitas seperti biasa, tetapi tetap waspada saat hujan deras dan lama.',
        todo: ['Beraktivitas seperti biasa.', 'Kenali rute evakuasi dan posko terdekat dari rumah Anda.', 'Siapkan tas siaga di rumah (dokumen, obat, senter, air minum).'],
        call: false,
    },
    WASPADA: {
        title: 'Waspada', icon: 'warning',
        card: 'bg-amber-50 border-amber-300', iconBox: 'bg-amber-500 text-white', titleColor: 'text-amber-800',
        desc: 'Tanah mulai jenuh air atau ada tanda pergerakan. Bersiaplah jika sewaktu-waktu harus mengungsi.',
        todo: ['Siapkan tas siaga dan letakkan di dekat pintu.', 'Jauhi tebing dan lereng curam, terutama saat hujan.', 'Pastikan lansia, anak-anak, dan anggota keluarga yang sakit siap dibawa.', 'Ikuti informasi dan arahan dari kader desa.'],
        call: true,
    },
    BAHAYA: {
        title: 'Bahaya: segera mengungsi', icon: 'directions_run',
        card: 'bg-rose-50 border-rose-300', iconBox: 'bg-rose-600 text-white', titleColor: 'text-rose-800',
        desc: 'Risiko longsor tinggi. Segera tinggalkan area lereng dan menuju posko evakuasi terdekat lewat rute yang sudah ditentukan.',
        todo: ['Tinggalkan rumah sekarang dan bawa tas siaga.', 'Ikuti rute evakuasi menuju posko, jangan mengambil jalan pintas.', 'Dahulukan lansia, anak-anak, dan penyandang disabilitas.', 'Sesampainya di posko, lapor (check-in) ke kader.'],
        call: true,
    },
    UNKNOWN: {
        title: 'Belum ada data terbaru', icon: 'help',
        card: 'bg-slate-50 border-slate-300', iconBox: 'bg-slate-400 text-white', titleColor: 'text-slate-700',
        desc: 'Sensor belum mengirim data terbaru. Jika hujan deras berlangsung lama, tetap waspada dan ikuti arahan kader desa.',
        todo: ['Perhatikan tanda alam: retakan tanah, rembesan air, suara gemuruh dari lereng.', 'Ikuti arahan kader desa.', 'Jika merasa terancam, segera menjauh dari lereng dan hubungi 112.'],
        call: true,
    },
};

const wHist = [];          // riwayat {t, moist, deep, pitch, roll, rain} untuk membandingkan 1 jam terakhir
let wLatest = null;

function wTime(ts) {
    if (!ts) return null;
    const d = new Date(String(ts).replace(' ', 'T'));
    return isNaN(d.getTime()) ? null : d;
}
function wNum(v) { const n = parseFloat(v); return isNaN(n) ? null : n; }
function wAgo(d) {
    if (!d) return 'belum ada data';
    const s = Math.max(0, (Date.now() - CLOCK_SKEW_MS - d.getTime()) / 1000);
    if (s < 60) return 'baru saja';
    if (s < 3600) return Math.floor(s / 60) + ' menit lalu';
    if (s < 86400) return Math.floor(s / 3600) + ' jam lalu';
    return Math.floor(s / 86400) + ' hari lalu';
}
function wPushHist(d) {
    if (!d) return;
    const t = wTime(d.timestamp);
    if (!t) return;
    if (wHist.length && wHist[wHist.length - 1].t.getTime() === t.getTime()) return;
    wHist.push({ t, moist: wNum(d.soil_moisture ?? d.moisture_percent), deep: wNum(d.moisture_percent_deep),
                 pitch: wNum(d.pitch), roll: wNum(d.roll), rain: wNum(d.rainfall_mm) });
    while (wHist.length > 2000) wHist.shift();
}
// data acuan: kira-kira 1 jam sebelum data terbaru (atau data paling awal yang tersedia)
function wRef() {
    if (wHist.length < 2) return null;
    const last = wHist[wHist.length - 1].t.getTime();
    let ref = wHist[0];
    for (const h of wHist) { if (last - h.t.getTime() <= 3600 * 1000) { ref = h; break; } }
    return ref === wHist[wHist.length - 1] ? null : ref;
}

function wSoilWord(v) {
    if (v === null) return ['Tidak terbaca', 'bg-slate-300', 'Sensor belum mengirim angka kelembapan.'];
    if (v > 75) return ['Sangat basah', 'bg-rose-500', 'Tanah sudah jenuh air. Tanah jenuh lebih mudah longsor.'];
    if (v > 55) return ['Basah', 'bg-amber-500', 'Tanah menyerap banyak air. Perlu diperhatikan bila hujan berlanjut.'];
    if (v > 30) return ['Lembap (normal)', 'bg-emerald-500', 'Kadar air tanah dalam batas wajar.'];
    return ['Kering', 'bg-emerald-500', 'Tanah kering, risiko longsor akibat air rendah.'];
}
function wRainWord(r) {
    if (r === null) return ['Tidak terbaca', 'bg-slate-300', 'Alat penakar hujan belum mengirim data.'];
    if (r > 50) return ['Hujan sangat lebat', 'bg-rose-500', 'Hujan sangat deras. Waspadai longsor dan banjir bandang.'];
    if (r > 20) return ['Hujan lebat', 'bg-rose-500', 'Hujan deras. Air cepat meresap ke tanah lereng.'];
    if (r > 5)  return ['Hujan sedang', 'bg-amber-500', 'Hujan cukup deras. Pantau terus bila berlangsung lama.'];
    if (r > 0)  return ['Hujan ringan', 'bg-emerald-500', 'Gerimis atau hujan ringan.'];
    return ['Tidak hujan', 'bg-emerald-500', 'Saat ini tidak ada hujan di lokasi sensor.'];
}
function wMoveWord(d, ref) {
    const p = wNum(d.pitch), r = wNum(d.roll);
    if (p === null && r === null) return ['Tidak terbaca', 'bg-slate-300', 'Sensor gerakan belum mengirim data.', null];
    if (!ref || ref.pitch === null) return ['Stabil', 'bg-emerald-500', 'Belum cukup data pembanding. Belum terlihat pergeseran.', null];
    const delta = Math.max(Math.abs((p ?? 0) - (ref.pitch ?? 0)), Math.abs((r ?? 0) - (ref.roll ?? 0)));
    if (delta >= 3) return ['Bergerak', 'bg-rose-500', 'Tiang sensor miring cukup besar dibanding sebelumnya. Bisa tanda tanah bergeser.', delta];
    if (delta >= 1) return ['Bergeser sedikit', 'bg-amber-500', 'Ada sedikit perubahan kemiringan. Kader akan memeriksa ke lapangan.', delta];
    return ['Stabil', 'bg-emerald-500', 'Tidak ada pergeseran berarti dibanding sebelumnya.', delta];
}

function wSetCard(id, word, dot, mean, raw, pinPct) {
    const card = document.getElementById(id);
    if (!card) return;
    card.querySelector('[data-w="word"]').textContent = word;
    card.querySelector('[data-w="dot"]').className = 'w-3 h-3 rounded-full ' + dot;
    card.querySelector('[data-w="mean"]').textContent = mean;
    card.querySelector('[data-w="raw"]').textContent = raw ? '(' + raw + ')' : '';
    const bar = card.querySelector('[data-w="bar"]');
    if (bar) {
        if (pinPct === null || pinPct === undefined) { bar.classList.add('hidden'); }
        else { bar.classList.remove('hidden'); bar.querySelector('[data-w="pin"]').style.left = 'calc(' + Math.max(0, Math.min(100, pinPct)) + '% - 7px)'; }
    }
}

function updateWarga(d) {
    if (!d) return;
    wLatest = d;
    wPushHist(d);
    const ref = wRef();

    // --- Status besar ---
    const key = (d.status_ai || d.status || '').toString().toUpperCase();
    const s = W_STATUS[key] || W_STATUS.UNKNOWN;
    const card = document.getElementById('wStatusCard');
    card.className = 'rounded-2xl border-2 p-5 sm:p-7 ' + s.card;
    const ib = document.getElementById('wStatusIcon');
    ib.className = 'w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-2xl flex items-center justify-center ' + s.iconBox;
    ib.innerHTML = '<span class="material-symbols-outlined text-[40px] sm:text-[48px]">' + s.icon + '</span>';
    const tt = document.getElementById('wStatusTitle');
    tt.textContent = s.title;
    tt.className = 'mt-1 text-[1.9rem] sm:text-[2.4rem] leading-tight font-extrabold ' + s.titleColor;
    document.getElementById('wStatusDesc').textContent = s.desc;
    document.getElementById('wTodo').innerHTML = s.todo.map((t, i) =>
        '<li class="flex items-start gap-3"><span class="w-6 h-6 shrink-0 rounded-full bg-deep-900 text-white text-[13px] font-bold flex items-center justify-center">' + (i + 1) + '</span><span>' + t + '</span></li>').join('');
    const call = document.getElementById('wCall112');
    call.classList.toggle('hidden', !s.call);
    call.classList.toggle('inline-flex', s.call);
    wRenderUpdated();

    // --- Kartu kondisi lereng ---
    const m = wNum(d.soil_moisture ?? d.moisture_percent), md = wNum(d.moisture_percent_deep), rain = wNum(d.rainfall_mm);
    let w = wSoilWord(m);  wSetCard('wSoil', w[0], w[1], w[2], m === null ? '' : m.toFixed(0) + '%', m);
    w = wSoilWord(md);     wSetCard('wDeep', w[0], w[1], w[2], md === null ? '' : md.toFixed(0) + '%', md);
    w = wRainWord(rain);   wSetCard('wRain', w[0], w[1], w[2], rain === null ? '' : rain.toFixed(1) + ' mm/jam', null);
    w = wMoveWord(d, ref); wSetCard('wMove', w[0], w[1], w[2], w[3] === null ? '' : 'berubah ' + w[3].toFixed(1) + '°', null);

    // --- Perubahan 1 jam terakhir ---
    const tr = [];
    if (ref && m !== null && ref.moist !== null) {
        const dm = m - ref.moist;
        tr.push(dm > 5 ? 'Tanah permukaan <b>makin basah</b> (naik ' + dm.toFixed(0) + ' poin).'
              : dm < -5 ? 'Tanah permukaan <b>mulai mengering</b> (turun ' + Math.abs(dm).toFixed(0) + ' poin).'
              : 'Kelembapan tanah permukaan <b>relatif tetap</b>.');
    }
    if (ref && md !== null && ref.deep !== null) {
        const dd = md - ref.deep;
        if (dd > 5) tr.push('Air sudah <b>meresap ke tanah bagian dalam</b> (naik ' + dd.toFixed(0) + ' poin).');
    }
    const rainyRecent = wHist.filter(h => ref && h.t >= ref.t && (h.rain ?? 0) > 0).length;
    if (ref) tr.push(rainyRecent ? 'Tercatat <b>hujan</b> di lokasi sensor dalam periode ini.' : '<b>Tidak tercatat hujan</b> di lokasi sensor.');
    if (!tr.length) tr.push('Belum cukup data untuk membandingkan dengan 1 jam sebelumnya.');
    document.getElementById('wTrend').innerHTML = tr.map(t => '<li class="flex items-start gap-2"><span class="text-teal-600 mt-0.5">•</span><span>' + t + '</span></li>').join('');
}

function wRenderUpdated() {
    const el = document.querySelector('#wUpdated span:last-child');
    if (!el || !wLatest) return;
    const d = wTime(wLatest.timestamp);
    const old = d && (Date.now() - CLOCK_SKEW_MS - d.getTime()) > 15 * 60 * 1000;
    el.innerHTML = 'Data sensor diperbarui ' + wAgo(d) + (old ? ' · <b class="text-amber-700">data sudah lama, sensor mungkin sedang tidak mengirim</b>' : '');
}

function updateWargaForecast(latest) {
    const el = document.getElementById('wForecast'), note = document.getElementById('wForecastNote');
    if (!el || !latest) return;
    const fs = (latest.forecast_status || '').toUpperCase();
    const now = (wLatest?.status_ai || '').toUpperCase();
    const word = { AMAN: 'Aman', WASPADA: 'Waspada', BAHAYA: 'Bahaya' }[fs];
    if (!word) { el.textContent = 'Belum tersedia'; note.textContent = 'Perkiraan muncul setelah sensor mengirim data cukup lama.'; return; }
    const rank = { AMAN: 0, WASPADA: 1, BAHAYA: 2 };
    let txt = word;
    if (now in rank) txt = rank[fs] > rank[now] ? 'Diperkirakan memburuk menjadi ' + word : rank[fs] < rank[now] ? 'Diperkirakan membaik menjadi ' + word : 'Diperkirakan tetap ' + word;
    el.textContent = txt;
    el.className = 'mt-0.5 text-[18px] sm:text-[20px] font-bold font-heading ' + (fs === 'BAHAYA' ? 'text-rose-700' : fs === 'WASPADA' ? 'text-amber-700' : 'text-emerald-700');
    const c = latest.rf_confidence;
    const sure = c == null ? '' : c >= 80 ? 'Komputer cukup yakin dengan perkiraan ini.' : c >= 60 ? 'Perkiraan ini cukup bisa dipegang, tetap pantau.' : 'Perkiraan ini belum pasti, tetap pantau perkembangan.';
    note.textContent = (sure + ' Perkiraan bisa berubah, keputusan mengungsi tetap mengikuti arahan kader.').trim();
}

function askQuick(q) {
    const input = document.getElementById('chatInput');
    if (!input) return;
    input.value = q;
    sendChat();
}

// ---- pilih tampilan: Warga / Data Teknis ----
function setLiveView(v) {
    const teknis = v === 'teknis';
    document.getElementById('wargaMain').classList.toggle('hidden', teknis);
    document.getElementById('teknisMain').classList.toggle('hidden', !teknis);
    document.querySelectorAll('.teknis-only').forEach(e => e.classList.toggle('hidden', !teknis));
    document.querySelectorAll('.warga-only').forEach(e => e.classList.toggle('hidden', teknis));
    document.querySelectorAll('[data-view-btn]').forEach(b => {
        const on = b.dataset.viewBtn === v;
        b.classList.toggle('bg-white', on); b.classList.toggle('text-teal-700', on); b.classList.toggle('shadow-sm', on);
        b.classList.toggle('text-deep-500', !on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    try { localStorage.setItem('larasLiveView', v); } catch (e) {}
    if (teknis) setTimeout(() => window.dispatchEvent(new Event('resize')), 50);
}

(function initWarga() {
    (phpLogs || []).forEach(wPushHist);
    if (phpCurrent) updateWarga(phpCurrent);
    let v = document.body.dataset.defaultView || 'warga';
    try { v = localStorage.getItem('larasLiveView') || v; } catch (e) {}
    setLiveView(v);
    setInterval(wRenderUpdated, 30000);
})();

// Buka Live Control Center langsung jika URL berakhiran #live (mis. dari navbar halaman lain)
if (location.hash === '#live') enterDashboard();
window.addEventListener('hashchange', () => {
    const liveVisible = document.getElementById('dashboard-page').style.display !== 'none';
    if (location.hash === '#live') {
        if (!liveVisible) enterDashboard();
    } else if (liveVisible) {
        // link menu seperti /#edukasi saat sedang di Live Control
        const target = location.hash.slice(1);
        target ? goToLandingSection(target) : backToLanding();
    }
});
</script>

</body>
</html>