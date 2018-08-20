<?php 

class RadarScoreCount
{
	public function __construct()
    {
    
    }
	public function getActivenessScore($activeness, $type = "IG"){
		$score = 0;
		//if($type == "FB"){
			if($activeness >= 9.23333){
				return 10;
			}else if($activeness >= 1.98666){
				return 9;
			}else if($activeness >= 1.4){
				return 8;
			}else if($activeness >= 1.02666){
				return 7;
			}else if($activeness >= 0.8){
				return 6;
			}else if($activeness >= 0.56666){
				return 5;
			}else if($activeness >= 0.42){
				return 4;
			}else if($activeness >= 0.27333){
				return 3;
			}else if($activeness >= 0.16666){
				return 2;
			}else if($activeness >= 0.66666){
				return 1;
			}else{
				return 0;
			}
		// }else{
		// 	if($activeness >= 5){
		// 		return 10;
		// 	}else if($activeness >= 1.933333){
		// 		return 9;
		// 	}else if($activeness >= 1.466667){
		// 		return 8;
		// 	}else if($activeness >= 1.193333){
		// 		return 7;
		// 	}else if($activeness >= 1){
		// 		return 6;
		// 	}else if($activeness >= 0.833333){
		// 		return 5;
		// 	}else if($activeness >= 0.7){
		// 		return 4;
		// 	}else if($activeness >= 0.54){
		// 		return 3;
		// 	}else if($activeness >= 0.4){
		// 		return 2;
		// 	}else if($activeness >= 0.26667){
		// 		return 1;
		// 	}else{
		// 		return 0;
		// 	}
		// }
		return $score;

	}
	public function getEngagementScore($engagement, $type = "IG"){
		$score = 0;
		if($engagement >= 10){
			return 10;
		}else if($engagement >= 5.26){
			return 9;
		}else if($engagement >= 3.76){
			return 8;
		}else if($engagement >= 2.93){
			return 7;
		}else if($engagement >= 2.44){
			return 6;
		}else if($engagement >= 2.04){
			return 5;
		}else if($engagement >= 1.73){
			return 4;
		}else if($engagement >= 1.44){
			return 3;
		}else if($engagement >= 1.12){
			return 2;
		}else if($engagement >= 0.82){
			return 1;
		}else{
			return 0;
		}
	}
	public function getInteractionScore($interaction, $type = "IG"){
		$score = 0;
		//if($type == "FB"){
			if($interaction >= 5000){
				return 10;
			}else if($interaction >= 2891.439394){
				return 9;
			}else if($interaction >= 1313){
				return 8;
			}else if($interaction >= 797.3043062){
				return 7;
			}else if($interaction >= 526.8109091){
				return 6;
			}else if($interaction >= 389.5833333){
				return 5;
			}else if($interaction >= 291.7272727){
				return 4;
			}else if($interaction >= 209.8757576){
				return 3;
			}else if($interaction >= 147.6545455){
				return 2;
			}else if($interaction >= 78.46363636){
				return 1;
			}else{
				return 0;
			}
		// }else{
		// 	if($interaction >= 5000){
		// 		return 10;
		// 	}else if($interaction >= 3597.121569){
		// 		return 9;
		// 	}else if($interaction >= 1672.367227){
		// 		return 8;
		// 	}else if($interaction >= 1026.162463){
		// 		return 7;
		// 	}else if($interaction >= 710.5159091){
		// 		return 6;
		// 	}else if($interaction >= 493.7777778){
		// 		return 5;
		// 	}else if($interaction >= 373.2792886){
		// 		return 4;
		// 	}else if($interaction >= 272.8888889){
		// 		return 3;
		// 	}else if($interaction >= 193.7425641){
		// 		return 2;
		// 	}else if($interaction >= 125.8762264){
		// 		return 1;
		// 	}else{
		// 		return 0;
		// 	}
		// }
		return $score;
	}

	public function getExplosivenessScore($explosiveness, $type = "IG"){
		$score = 0;
		//if($type == "FB"){
			if($explosiveness >= 3429.493941){
				return 10;
			}else if($explosiveness >= 723.0726817){
				return 9;
			}else if($explosiveness >= 280.1060668){
				return 8;
			}else if($explosiveness >= 221.7152891){
				return 7;
			}else if($explosiveness >= 165.0868551){
				return 6;
			}else if($explosiveness >= 124.1633199){
				return 5;
			}else if($explosiveness >= 93.00440288){
				return 4;
			}else if($explosiveness >= 67.8712171){
				return 3;
			}else if($explosiveness >= 36.45222513){
				return 2;
			}else if($explosiveness >= -5.81145782){
				return 1;
			}else{
				return 0;
			}
		// }else{
		// 	if($explosiveness >= 300){
		// 		return 10;
		// 	}else if($explosiveness >= 182.7437926){
		// 		return 9;
		// 	}else if($explosiveness >= 128.9108685){
		// 		return 8;
		// 	}else if($explosiveness >= 102.1021077){
		// 		return 7;
		// 	}else if($explosiveness >= 83.65496549){
		// 		return 6;
		// 	}else if($explosiveness >= 70.44737764){
		// 		return 5;
		// 	}else if($explosiveness >= 59.34632945){
		// 		return 4;
		// 	}else if($explosiveness >= 45.68175097){
		// 		return 3;
		// 	}else if($explosiveness >= 34.39558175){
		// 		return 2;
		// 	}else if($explosiveness >= 19.43343344){
		// 		return 1;
		// 	}else{
		// 		return 0;
		// 	}
		// }
		return $score;
	}

	public function getReachScore($reach, $type = "IG"){
		$score = 0;
		//if($type == "FB"){
			if($reach >= 500000){
				return 10;
			}else if($reach >= 112982.9){
				return 9;
			}else if($reach >= 59646.6){
				return 8;
			}else if($reach >= 36750){
				return 7;
			}else if($reach >= 24901.4){
				return 6;
			}else if($reach >= 17838.5){
				return 5;
			}else if($reach >= 13598){
				return 4;
			}else if($reach >= 10608.9){
				return 3;
			}else if($reach >= 8089.4){
				return 2;
			}else if($reach >= 6361.6){
				return 1;
			}else{
				return 0;
			}
		// }else{
		// 	if($reach >= 500000){
		// 		return 10;
		// 	}else if($reach >= 139773.6){
		// 		return 9;
		// 	}else if($reach >= 80883){
		// 		return 8;
		// 	}else if($reach >= 52781.8){
		// 		return 7;
		// 	}else if($reach >= 33850.6){
		// 		return 6;
		// 	}else if($reach >= 25241){
		// 		return 5;
		// 	}else if($reach >= 17891.4){
		// 		return 4;
		// 	}else if($reach >= 14337.6){
		// 		return 3;
		// 	}else if($reach >= 10656){
		// 		return 2;
		// 	}else if($reach >= 7492){
		// 		return 1;
		// 	}else{
		// 		return 0;
		// 	}
		// }
		return $score;
	}

	public function getAppealScore($appeal, $type = "IG"){
		$score = 0;
		//if($type == "FB"){
			if($appeal >= 0.356069){
				return 10;
			}else if($appeal >= 0.011263){
				return 9;
			}else if($appeal >= 0.006815){
				return 8;
			}else if($appeal >= 0.004701){
				return 7;
			}else if($appeal >= 0.003155){
				return 6;
			}else if($appeal >= 0.002234){
				return 5;
			}else if($appeal >= 0.00155){
				return 4;
			}else if($appeal >= 0.000848){
				return 3;
			}else if($appeal >= 0.000281){
				return 2;
			}else if($appeal >= 0.0001){
				return 1;
			}else{
				return 0;
			}
		// }else{
		// 	if($appeal >= 2){
		// 		return 10;
		// 	}else if($appeal >= 1.454559659){
		// 		return 9;
		// 	}else if($appeal >= 0.739890313){
		// 		return 8;
		// 	}else if($appeal >= 0.472776262){
		// 		return 7;
		// 	}else if($appeal >= 0.320596524){
		// 		return 6;
		// 	}else if($appeal >= 0.214956088){
		// 		return 5;
		// 	}else if($appeal >= 0.142043577){
		// 		return 4;
		// 	}else if($appeal >= 0.076054488){
		// 		return 3;
		// 	}else if($appeal >= 0.017763885){
		// 		return 2;
		// 	}else if($appeal >= -0.049069904){
		// 		return 1;
		// 	}else{
		// 		return 0;
		// 	}
		// }
		return $score;
	}
}