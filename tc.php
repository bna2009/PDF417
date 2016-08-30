<?php

class Tccode
{
	var $str;  //��Ҫʹ���ı�ѹ�����ַ�����
	var $cur;  //��ǰѹ��λ�ã�
	var $length; //�ַ�������
    var $Tccurmode; //��ǰ��ģʽ��ȡֵ��Χ Alpha , Lower ,Mix , Punc
	function __construct($str)
	{
		$this->str = str_split($str);
		$this->string = $str;
		$this->cur = 0;
		$this->length = count($this->str);
		$this->Tccurmode = 'Alpha';   //����TCѹ����Ĭ����ģʽΪ��д��ĸ�ͣ�
	}

	//�����ַ���
	function getnextchar($step = 1, $reread = 0)
	{
		$ret = array();
		//echo $this->str[0];
		for($i = $this->cur; $i < $this->cur + $step; $i ++)
		   $ret[] = $this->str[$i];
        if(!$reread)
		{
		   $this->cur += $step;
		}
		return implode('', $ret);
	}
	function getcode()
	{
	   //print_r($this->str);
	   $Tcarr = array();
       while( $this->cur < $this->length )
		{
		  $str = $this->getnextchar();  //��ȡ1���ַ���
		  $child = $this->getChildMode($str); //��ȡ�ַ���ģʽ��
          if( $child  == $this->Tccurmode )  $Tcarr[] = asciitotc($str,$child);   //�͵�ǰ��ģʽ��ͬ
		  else  
			{
			  $switch = $this->getSwitchSymbol( $this->Tccurmode , $child );  //��ȡ�л������飬����������л������п���Ϊ��������������������
			  if(@array_pop($switch) == 1 )  //������
				{
				  $this->Tccurmode = $child;
				}
				$Tcarr = array_merge($Tcarr, $switch);
				$Tcarr[] = asciitotc($str,$child);
			} 
		}
		// print_r($Tcarr);
		if(count($Tcarr)%2) $Tcarr[] = 29; 
		for($i = 0; $i < count($Tcarr)/2; $i++)
		{
			$code[$i] = $Tcarr[$i*2] * 30 + $Tcarr[$i * 2 + 1];
		}
		//echo "<hr />TC code str:{$this->string}<br />";
	 // print_r($code);  
	  return $code;
	}
 
    //����һ���ַ�����ģʽ; ���ڱ�����ַ����ֽ��٣���һ���ַ�ͬʱ���ڻ���ͺͱ����ʱ�����ַ��������͡�
	function getChildMode($str) 
	{
		$return = '';
		$ascii = ord($str);
		//echo $str."  ascii:".$ascii."  ";
		//echo "curmode:".$this->Tccurmode."  ";
		$mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //������ַ�ASCII�б�
		$Punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //������ַ�ASCII�б�
		$rep  = array(13,09,44,58,45,46,36,47,42); //����������� �ظ��ַ��б�
		if( ($ascii >= 65 & $ascii <= 90) ||  ($this->Tccurmode == 'Alpha' & $ascii == 32 )  )  $return = "Alpha";  
		else if( ($ascii >= 97 & $ascii <= 122) || ($this->Tccurmode =='Lower' & $ascii== 32))  $return = "Lower";  
		else if( in_array($ascii,$mix) || in_array($ascii,$rep) || ($this->Tccurmode =='Mix' & $ascii== 32))
		   $return = "Mix";  
		else if(in_array($ascii,$Punc))
		   $return = "Punc"; 
		else if( $ascii == 32 ) return "Lower";
		else $return = "No"; 
		 return $return;
	}


	
    //���ش���ģʽ1 �л�����ģʽ2����ӵ��л��ַ����飻�����ʽ��
	// array (
    //         �л���1��
	//         �л���2������
	//         ����   )  (����ֵ�� 1������   0��ת��   2��Error)
	function getSwitchSymbol ( $mode1 , $mode2 ) 
	{
		$curmode = array();
		$lock = 1;   //Ĭ��Ϊģʽ����
	  if( $mode1 == 'Alpha' )  //��д��ĸ���Ӵ������
		{
           if(  $mode2 == "Lower" ) $curmode[] = 27;
		   else if( $mode2 == "Mix" ) $curmode[] = 28;
		   else if( $mode2 == "Punc" )
			{
			   $str = $this->getnextchar(1,1);  //Ԥ��ȡ1���ַ���
		       $child = $this->getChildMode($str); //��ȡ�ַ���ģʽ��
			   if( $child  == "Punc" )
				{
				   $curmode[] = 28;
				   $curmode[] = 25;
				}
				else 
				{
					$curmode[] =29;
					$lock = 0;    //��ʾģʽת����
				}
			}
		   else  $curmode[] = "error";
		   $curmode[] = $lock;
		   return $curmode;
		}
		else if( $mode1 == 'Lower')  //Сд��ĸ���Ӵ������
		{
		   if(  $mode2 == "Mix" ) $curmode[] = 28;
		   else if( $mode2 == "Alpha" )
			{
               $str = $this->getnextchar(1,1);  //Ԥ��ȡ1���ַ���
		       $child = $this->getChildMode($str); //��ȡ�ַ���ģʽ��
			   if( $child  == "Alpha" )
				{
				   $curmode[] = 28;  
				   $curmode[] = 28;   //��ʾģʽ������
				}
				else 
				{
					$curmode[] =27;
					$lock = 0;    //��ʾģʽת����
				}
			}
		   else if( $mode2 == "Punc" )
			{
			   $str = $this->getnextchar(1,1);  //Ԥ��ȡ1���ַ���
		       $child = $this->getChildMode($str); //��ȡ�ַ���ģʽ��
			   if( $child  == "Punc" )
				{
				   $curmode[] = 28;
				   $curmode[] = 25;
				}
				else 
				{
					$curmode[] =29;
					$lock = 0;    //��ʾģʽת����
				}
			}
		   else  $curmode[] = 2;
		   $curmode[] = $lock;
		   return $curmode;
		}
		else if( $mode1 == 'Mix') //��ȫ���Ӵ������
		{
			if(  $mode2 == "Lower" ) $curmode[] = 27;
			else if ( $mode2 == "Alpha" ) $curmode[] = 28;
			else if( $mode2 == "Punc" )
			{
			   $str = $this->getnextchar(1,1);  //Ԥ��ȡ1���ַ���
		       $child = $this->getChildMode($str); //��ȡ�ַ���ģʽ��
			   if( $child  == "Punc" )
				{
				   $curmode[] = 25;
				}
				else 
				{
					$curmode[] = 29;
					$lock = 0;    //��ʾģʽת����
				}
			}
			else  $curmode[] = "no switch!";
			$curmode[] = $lock;
		    return $curmode;
		}
		else if( $mode1 == 'Punc') //������Ӵ������
		{
			 $curmode[] = 29;
			 if ( $mode2 == "Alpha" ) ;
			 else if($mode2 == "Lower") $curmode[] = 27;
			 else if( $mode2 == "Mix" ) $curmode[] = 28;
			 else  $curmode[] = "no switch!";
			 $curmode[] = $lock;
		     return $curmode;
		}
	}
}

?>