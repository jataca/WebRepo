<?php header('Content-type: text/css'); ?>

/* Basic Styles */
.feedburner-basic {
	overflow: hidden;
}
.feedburner-basic a.feed-button {
	display: block; float: left;
	width: 78px; height: 78px;
	margin: 0 10px 0 0; padding: 0;
	background: url(../images/rss.gif) no-repeat;
	text-indent: -9999px;
	overflow: hidden;
}
.feedburner-basic p {
	clear: both;
	font-size: 11px;
	margin: 0; padding: 10px 0;
}
.feedburner-basic form {
	margin: 5px 0 0 0; padding: 5px 0 0 0;
}
.feedburner-basic .input-text {
	display: block; float: left;
	margin: 0 0 5px 0; padding: 5px;
	border: 1px solid #a5a5a5;
	width: 60%
}
.feedburner-basic .input-submit {
	display: block; float: left;
	width: 140px; height: 32px;
	margin: 0 0 5px 0; padding: 0;
	background: url(../images/submit-bg.gif) no-repeat;
	color: #613827;
	text-align: center;
	text-transform: uppercase;
	font-size: 12px;
	font-weight: bold;
	border: none;
}

/* Light Styles */
.feedburner-light {
	margin: 0; padding: 10px;
	background: #FFF url(../images/feedwidget-bg.gif) bottom left no-repeat;
	border: 1px solid #e8e8e8;
	overflow: hidden;
}
.feedburner-light a.feed-button {
	display: block; float: left;
	width: 78px; height: 78px;
	margin: 0 10px 0 0; padding: 0;
	background: url(../images/rss.gif) no-repeat;
	text-indent: -9999px;
	overflow: hidden;
}
.feedburner-light p {
	color: #333;
	clear: both;
	font-size: 11px;
	margin: 0; padding: 10px 0;
}
.feedburner-light form {
	margin: 5px 0 0 0; padding: 5px 0 0 0;
}
.feedburner-light .input-text {
	display: block; float: left;
	margin: 0 0 5px 0; padding: 5px;
	border: 1px solid #a5a5a5;
	width: 60%
}
.feedburner-light .input-submit {
	display: block; float: left;
	width: 140px; height: 32px;
	margin: 0 0 5px 0; padding: 0;
	background: url(../images/submit-bg.gif) no-repeat;
	color: #613827;
	text-align: center;
	text-transform: uppercase;
	font-size: 12px;
	font-weight: bold;
	border: none;
}

/*
this stuff makes small changes depending 
on what widget area we're in 
*/

#feature-top .feedburner-light,
#feature-bottom .feedburner-light {
	margin: 10px 10px 0 10px;
}

.w180- .feedburner-basic,
.w180- .feedburner-light,
.w180- .feedburner-dark {
	text-align: center;
}
.w180- a.feed-button,
.w180- .input-text,
.w180- .input-submit {
	float: none;
	margin: 5px auto;
}
.w180- .input-text {
	width: 85%
}