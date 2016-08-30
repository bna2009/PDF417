<?php
 include('tc.php');
 include('bc.php');
 include('nc.php');
 include('image.php');
 include_once('common.func.php');
class makecode
{
	var $str, $length;       //��Ҫ������ַ���,���ַ������ȣ�
	var $row;       //������
	var $i;         //��ǰ������
	var $s;         //����ȼ���
	var $cow;       //������������
	var $correct_c;  //�������ָ���
	var $padding_c;  //������ָ�����
	var $curstr ;      //�ַ���ǰλ�ñ�ʶ��
	var $curmode;      //��ǰ��ѹ��ģʽ,0:����ѹ�� 1���ı�ѹ�� 2���ֽ�ѹ��
	var $n ,$t, $b;   //$n Ϊ�������ָ��� �� $t Ϊ�����ı������� $b Ϊ�����ֽڸ����� $p ָʾ��ǰλ�ã� 
	var $data;        //��������
	var $nextmode = '';
	private $img;
	function __construct($string='', $s = -1) //��Ҫ������ַ������;���ȼ�
	{
		//echo "<h1>��Ҫ������ַ�����<br />".$string."</h1>";
		$this->str = $string;
		$this->length = strlen($this->str); 
		$this->s = $s;
		$this->strarray = str_split($string);
		$this->curstr = 0;          //��ȡ��ʶ ��
		$this->curmode = 1;         //Ĭ��Ϊ�ı�ѹ��
		$this->img = new createimg();
		$this->data = $this->getdata();  //�����������������,����Ƽ�������
		$this->correct_c = Exponentiation(2,$this->s + 1); //����������ָ�����
		$this->countrow();          //����������
		$this->countpadinglength(); //��������ַ�������
		$this->getcorrect();        //����������֣�
		$this->img->setrow($this->row, $this->cow, $this->s);   //����ͼƬʵ����
		$this->createimg();
	}

	function countDataLength()   //�������ָ����� ���������ų������� + ���������� + �������֣�
     {
       $count = count($this->data);
	   return $count + 1 + $this->correct_c; 
     }

     function countpadinglength()  //����������ָ�����
      {
		 $this->padding_c = $this->cow * $this->row - $this->countDataLength();
		 $str = "\r\n����ַ�����Ϊ��".$this->padding_c ; 
		 $str .= "\r\n����ȼ�Ϊ��".$this->s ;
		 $str .= "\r\n������".$this->row ; 
		 $str .= "\r\n������".$this->cow ;
	     fwrite($this->img->f, $str);
      }
 
	function countrow()  //���ÿ�߱�Ϊ3��1 �������������ָ���������ߣ�
	{
	   $row = intval(sqrt($this->countDataLength()));    //�������� 
	   $cow =  $row;                                     //��������
	   if( $this->countDataLength() - $row * $row  > 0 ) $row +=  (intval(($this->countDataLength() - $cow * $cow  - 1) / $cow) + 1);  
	   if($row < 3) 
		{
		   $row = 3;
		   $cow = 3;
		}
	   else if($cow > 30) 
		   {
		      $cow = 30;
			  $row = (intval(($this->countDataLength() - 1) / $cow) + 1); 
		   }
      //echo "<br />row:".$row."   cow:".$cow."<br />"; exit();
	  $this->row = $row;
	  $this->cow = $cow; 
	}
	
	function getcorrect()
	{
		$count = count($this->data) + 1 + $this->padding_c;  //�������ָ����� ���������ų������� + ���������� + ������֣�
		array_unshift($this->data, $count);
		$this->data = array_pad($this->data, $count, 900);
		$this->data = array_merge($this->data, ErrorCorrection($this->data, $this->s));
		foreach($this->data as $key => $value)
			$data[$key/$this->cow][] = $value; 
		return $this->data = $data;
	 
	}

   function createimg()
	{
	   // print_r($this->data); 
	   $str = "\r\n\r\n��������Ϊ��".$this->str; 
	   fwrite($this->img->f, $str);
	   foreach($this->data as $key)
		{
		    fwrite($this->img->f, "\r\n");
			foreach($key as $value)
				fwrite($this->img->f, $value."   ");
		}
	   $str = "\r\n\r\nת����01����Ϊ:"; 
	   fwrite($this->img->f, $str);
	   foreach($this->data as $i => $row )
		{
		   $this->img->leftline($i + 1);
		   foreach ($row as $key => $value)
			{
			    $this->img->makepic(getbs($i % 3 *3, $value));
			}
		   $this->img->rightline($i + 1);
		   //exit();
		}
		fclose($this->img->f);
	    $this->img->echoimg();
	}
	
    //���ص�ǰ�ַ�,��Ϊ����ַ�����Ϊ�ַ����飻$setp Ϊ��ȡ�ַ�������reread = 0��ʾ����Ԥ��(����ָʾ��curstr������) ,$startΪ��ʼ��ȡλ�ã�
	function getchar($step = 1, $reread = 0 , $start = -1 )
	{
		if($start == -1 ) $start = $this->curstr;
		if(($start + $step) > strlen($this->str))  return false;  
		for($i = $start; $i < $start + $step; $i++)
		   $ret .= $this->strarray[$i];
        if(!$reread)
		{
		   $this->curstr += $step;
		} 
        return $ret;
	}

	function getdata()
	{
		$code = array();
		$p = 0;  //�ѱ���λ�ñ�ʶ
		while($p < $this->length && count($code) < 900)  //�ж��Ƿ����
		{
			$this->n = 0; $this->t = 0; $this->b = 0; 
			//echo "curstr:".$this->curstr;
			//$n Ϊ�������ָ��� �� $t Ϊ�����ı������� $b Ϊ�����ֽڸ����� 
			while( $this->curstr < $this->length & is_number($this->getchar()))
			   $this->n++;  
			if($this->n >= 13) 
				{
				if($this->curmode != 0) 
					{
						$this->curmode = 0;
						$code[] = 902;
					}
				
				$ncarr = new Nccode($this->getchar($this->n,1,$p));
				$code = array_merge($code, $ncarr->getcode());
				$str = "\r\nNCģʽ���� ��".$this->getchar($this->n,1,$p)."\r\n"; 
				$result = "�������� ".var_export($ncarr->getcode(),TRUE)."\r\n";
	            fwrite($this->img->f, $str.$result);
				$p += $this->n;
				$this->curstr = $p;
				}
			else 
				{
					$this->curstr = $p; $this->t = 0;
					while( $this->curstr < $this->length & $this->is_text($this->getchar()))
						$this->t ++;
					if($this->t >= 5)
					{
						if(!($this->curmode == 1)) 
							{
								$this->curmode = 1;
								$code[] = 900;
							}
						
						$tcarr = new Tccode($this->getchar($this->t,1,$p));
						$tcresult = $tcarr->getcode();
						$str = "\r\nTCģʽ���� ��".$this->getchar($this->t,1,$p)."\r\n"; 
						$result = "�������� ".var_export($tcresult,TRUE)."\r\n";
						$code = array_merge($code, $tcresult);
						fwrite($this->img->f, $str.$result);
						$p += $this->t;
						$this->curstr = $p;	
					}
					else
					{ 
						$this->curstr = $p;
						while($this->curstr < $this->length & $this->is_bc($this->getchar()) )
							$this->b ++;
						if($this->b == 1 & $this->curmode == 1 )
						{
							$code[] = 913;  
						}
						else 
						{
							if($this->curmode != 2) 
								{
									$this->curmode = 2;
									if($this->b % 6 ) $code[] = 901;
									else $code[] = 924;
								}
						}
						$bcarr = new Bccode($this->getchar($this->b,1,$p));
						$code = array_merge($code, $bcarr->getcode());
						$str = "\r\nBCģʽ���� ��".$this->getchar($this->b,1,$p)."\r\n"; 
						$result = "�������� ".var_export($bcarr->getcode(),TRUE)."\r\n";
						fwrite($this->img->f, $str.$result);
						$p += $this->b;
						$this->curstr = $p;
					}
				}
		}
 
        if(count($code) > 898 ) 
		{
			$beyond  = count($code) - 898;
			for($i =1; $i <=$beyond; $i++ )
			{
				array_pop($code);
			}
		}	 
		if( $this->s == -1 ) $this->s = $this->gets($code);
	    return $code;		
	}
	function gets($data)
	{
		 $count = count($data);
		 if($count < 40 ) $s = 2;
		 else if( $count < 160 ) $s = 3;
		 else if( $count < 320 ) $s = 4;
		 else if( $count < 863 ) $s = 5;
		 else $s = 6;
		 return $s;
	}

 
	function is_text($str)
	{
		$mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //������ַ�ASCII�б�
		$punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //������ַ�ASCII�б�
		$rep  = array(13,09,44,58,45,46,36,47,42,32); //����������� �ظ��ַ��б� �����32���ո�
		$alpha = range(65,90);
		$lower = range(97,122);
		if(is_number($str)) 
		{
			if(is_number($this->getchar(12,1))) return false;  
		}
		
		$ascii = ord($str);
		if(($ascii >= 65 & $ascii <= 90) || ($ascii >= 97 & $ascii <= 122) || in_array($ascii, $mix) || in_array($ascii, $punc) || in_array($ascii, $rep)) 
		   return true;  
		else  return false;  
	}


	function is_bc($str)
	{
		$mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //������ַ�ASCII�б�
		$punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //������ַ�ASCII�б�
		$rep  = array(13,09,44,58,45,46,36,47,42,32); //����������� �ظ��ַ��б� �����32���ո�
		$alpha = range(65,90);
		$lower = range(97,122);
		$text = array_merge($mix, $punc, $rep, $alpha, $lower);
		if(is_number($str)) 
		{
			if(is_number($this->getchar(12,1))) { $this->nextmode = 'NC'; return false; } 
		}
		if(in_array(ord($str), $text))
		{
			
			$this->t = 1;  
			$p = $this->curstr;
			for($i = 1; $i < 5; $i ++)
			{
				if($this->curstr < $this->length)
				{
					$st = $this->getchar();
					if(in_array(ord($st), $text) )
					{
						$this->t++;
					}
					if(is_number($st))
					{
						if(is_number($this->getchar(12,1))) 
							{ $this->t--; break; } 
					}
				}
			}
			$this->curstr = $p;
			if($this->t++ >= 5 )
				{
					$this->nextmode = 'TC'; return false; 
			    }
			else return true;
		}
		else  return true;
	} 
}

if($_POST['submit'] & $_POST['cont']) 
	{
		$cont = $_POST['cont']; 
		echo "<title>PDF417����ɹ���</title>";
		$code = new makecode($cont, 1);
	}
	else 
	{
		header("location:./index.html");
	}
?>
