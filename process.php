<?php

function ParseOhysTimetable($optYear, $optQuarter){
    
    $days = [
            '월', '화', '수', '목', '금', '토', '일', 'SP',
            '月', '火', '水', '木', '金', '土', '日', 'SP',
            'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN', 'SP'
        ];
    $dividers = [
            '/', ']', '['
        ];
    $comments = [
            '//', '/{', '/ [', ' / '
        ];

    $filename = "https://raw.githubusercontent.com/ohyongslck/annie/master/$optYear@$optQuarter";

    //if(file_exists($filename)){
        $fileContents = file_get_contents($filename); //Get the file
        $rows = explode("\n", $fileContents); //Split the file by each line

        $returnData = [];

        $day=0;
        foreach($rows as $row){
            if(!$row) continue;
            for($k=0;$k<count($days);$k++){
                if(startsWith($row, $days[$k])){
                    $day=$k%8;
                    continue;
                }
            }

            $title=["promised"=>"", "English"=>"", "Korean"=> "", "Japanese"=>""];
            $pruned = $row;
            $comment='';
            $date='';
            $time='';

            foreach($comments as $com){
                $pos=strpos($pruned, $com);
                if($pos){
                    $subLines = explode($com, $pruned);
                    $pruned=$subLines[0];
                    $comment=$subLines[1];
                }
            }
            
            foreach($dividers as $divider){
                $tokens = array_reverse(explode($divider, $pruned));
                $z=count($tokens);
                for($n=0;$n<$z;$n++){
                    $tokens[$n]=trim($tokens[$n]);
                    $token = trim(substr($tokens[$n], 1));

                    if(!$time){
                        preg_match('/\d{1,2}:\d{1,2}/i', $token, $possible);
                        if($possible){
                            $time = $possible[0];
                        }
                    }elseif(!$date){
                        preg_match('/(\d{4}\/\d{1,2}\/\d{1,2})|(\d{1,2}\/\d{1,2})/', $token, $possible);
                        if($possible){
                            $date = $possible[0];
                        }
                    }elseif($token){
                        preg_match('/[a-zA-Z가-힣一-龠ぁ-ゔァ-ヴーａ-ｚＡ-Ｚ０-９々〆〤]/u', $token, $possible);
                        if($possible){
                            if(!$title["promised"]){
                                $title["promised"]=$token;
                            }
                            preg_match('/[a-z]/i', $token, $match);
                            if(!$title["English"] && $match){
                                $title["English"]=$token;
                            }
                            preg_match('/[가-힣]/ui', $token, $match);
                            if(!$title["Korean"] && $match){
                                $title["Korean"]=$token;
                            }
                            preg_match('/[一-龠ぁ-ゔァ-ヴーａ-ｚＡ-Ｚ０-９々〆〤]/ui', $token, $match);
                            if(!$title["Japanese"] && $match){
                                $title["Japanese"]=$token;
                            }
                        }
                    }
                }
            }

            if(!$title["promised"]){
                continue;
            }

            $returnData[]=(object)[
                "year"=>$optYear,
                "quarter"=>$optQuarter,
                "day"=>$day,
                "date"=>$date,
                "time"=>$time,
                "name"=>(object)$title,
                "comment"=>$comment,
                "original"=>$row
            ];
        }
        return $returnData;
    // }else{
    //     return ["File not exists"];
    // }
    
}
function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
} 
?>