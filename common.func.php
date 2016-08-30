<?php
include('common.data.php');
function switch256to900($arr)  //��6���ֽ�ת����5������,����Ϊ�������6��Ԫ�ص����飻
{
	 $dec=0;
	 $return = array();
     if(count($arr)<6) return $arr;   //����������� ����6��Ԫ�أ�����ԭ�����飻
	 if(count($arr)>6) { echo "��������"; exit(); } //����������鳬��6��Ԫ�أ�������ʾ����ֹ����
	 foreach ($arr as $key=>$val)
	{   
    $dec = bcadd($dec, bcmul( $val,Exponentiation(256,5-$key)));
    }
	 for($i=0; $i < 5; $i++)
	{
		 $return[$i]= bcmod($dec,900);
		 $dec= bcdiv($dec , 900);
	}
	return array_reverse($return);
}

function switch10to900($arr) //��44λʮ��������ת����15������,����Ϊ�������44��Ԫ�ص����飻��ǰ��λ1��
{
  // $str="000213298174000";  //�������ݣ�
  //$arr = str_split($str);
  array_unshift($arr,"1");
  $dec = implode($arr); 
  $length = intval((strlen($dec)-1)/3)+1;  //���ֳ��ȣ�
  for($i=0; $i < $length; $i++)
	{
		 $return[$i]= bcmod($dec,900);
		 $dec= bcdiv($dec , 900);
		 //echo $return[$i]."dec:".$dec."<br />";
	}
   return array_reverse($return);
}

function Exponentiation($x,$s) //������,x��s���ݣ�
{
	$r=1.0;
	if($x==1 || $x==0 || $s ==0) return 1;
	for($i=0;$i<$s;$i++)
		$r = bcmul($r,$x,50);
	return $r;
}

function is_number($str)
	{
		if(strlen($str)>1)
		{
			$arr = str_split($str);
			$strint = '';
			foreach ($arr as $key => $value)
				{
					$strint .= intval($value).'';
				}
			if($str == $strint) return true;
			else return false; 
		}
		else 
		{
			if(trim($str) === '') return false;
			$strint = intval($str).'';
			if($str == $strint) return true;
			else return false; 
		}
		
	}

	function asciitotc($str, $mode)
	{
	
	$mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //������ַ�ASCII�б�
	$Punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //������ַ�ASCII�б�
	$rep  = array(13,09,44,58,45,46,36,47,42); //����������� �ظ��ַ��б�

	$mixcode  = array(0,1,2,3,4,5,6,7,8,9,10,15,20,21,23,24);
	$punccode = array(0,1,2,3,4,5,6,7,8,9,10,15,20,21,23,24,25,26,27,28);
	$repcode  = array(11,12,13,14,16,17,18,19,22);
	$ascii = ord($str);
	if($mode == 'Alpha')
		{
		  if($ascii == 32) $ret = 26;
		  else $ret = ord($str) - 65 ;
		}
    else if($mode == 'Lower')
		{
		  if($ascii == 32) $ret = 26;
		  else $ret = ord($str) - 97 ;
		}
	else if($mode == 'Mix')
		{
		   if($ascii == 32) $ret = 26;
		   else if(in_array($ascii,$mix))
			{
			   
				$key =  array_keys($mix, $ascii);
				$ret = $mixcode[$key['0']];
			}
			else if(in_array($ascii,$rep))
			{
				$key =  array_keys($rep, $ascii);
				$ret = $repcode[$key['0']];
			}
		}
	else if($mode == "Punc")
		{
		   if(in_array($ascii,$Punc))
			{
				$key =  array_keys($Punc, $ascii);
				$ret = $punccode[$key[0]];
			}
			else if(in_array($ascii,$rep))
			{
				$key =  array_keys($rep, $ascii);
				$ret = $repcode[$key];
			}
		}
     return $ret;
	}

function bushu($m)
{ 
	return 929 - $m;
}
function ErrorCorrection($codearray,$correctleval)  //���ݣ�������
 {
	 $array = array();
	 $k = Exponentiation(2, $correctleval+1);    //�����������
	 $c = array_pad($array, $k, 0);  //����������
	 $t1 = $t2 = $t3 = 0;                       //��ʱ����
	 $a = geta($correctleval);            //���ݾ����𣬵ó���g(x)չ��������ϵ����
	 foreach($codearray as $key => $data)
	 { 
		 $t1 = ( $data + $c[$k - 1] ) % 929 ;
		 // echo "    t1:$t1\n";
		 foreach($c as $keyc => $correct)
		 {
			$t2 = ($t1 * $a[$k - $keyc - 1]) % 929;
			$t3 =  929 - $t2;
			if($k - $keyc - 1 == 0)
				$m = $t3;
			else $m = $c[$k - $keyc - 2] + $t3;
			$c[$k - $keyc - 1] =  $m % 929; 
			//echo "t2:".$t2."      t3:".$t3."        c[".($k - $keyc - 1)."]:".$c[$k - $keyc - 1]."\n";
		 }
	 }
	$c = array_map("bushu", array_reverse($c));
	//print_r($c);echo "<hr />";
	return $c;
 }
?>