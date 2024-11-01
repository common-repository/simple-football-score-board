var j = jQuery.noConflict();
j(function () {
	j('#YTMRFBScoreBoard_form input[type=color]').on('change', function(){
		var name = j(this).attr('name');
		if(name == 'YTMRFBScoreBoard[background_color][v]'){
			j('div#YTMRFBScoreBoard').css('background-color', j(this).val());
		}
		else if(name == 'YTMRFBScoreBoard[text_color_name][v]'){
			j('div#YTMRFBScoreBoard td#tm_name').css('color', j(this).val());
		}
		else if(name == 'YTMRFBScoreBoard[text_color_score][v]'){
			j('div#YTMRFBScoreBoard div.inner td').css('color', j(this).val());
		}
		else if(name == 'YTMRFBScoreBoard[border_line_color][v]'){
			j('div#YTMRFBScoreBoard').css('border-color', j(this).val());
		}
		else if(name == 'YTMRFBScoreBoard[box_color][v]'){
			j('div#YTMRFBScoreBoard div.inner').css('background-color', j(this).val());
		}
	});
	j('#YTMRFBScoreBoard_form select').on('change', function(){
		j('div#YTMRFBScoreBoard').css('border-width', j(this).val());
	});
});
