<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PthRankingViewGraphics extends JViewLegacy
{
    


    function display($tpl = null)
    {
        $error=0;
		$jinput = JFactory::getApplication()->input;
        $type = $jinput->get('t', 0, 'INT');
        if($type!=1) $type=0;
        $data = $jinput->get('d', "", 'STRING');
        $pc=explode(",",$data);
        // start copypasting

        if(count($pc)!=10) $error=1;
        $sum=0;
        for($i1=0;$i1<10;$i1++)
        {	
            $pc[$i1]=(int)$pc[$i1];
        	if($pc[$i1]<0) $error=1;
        	$sum+=$pc[$i1];
        }
        $deg=array();
        if($sum==0) $error=1;
        for($i1=0;$i1<10 and $error==0;$i1++) $deg[$i1]=360.0*$pc[$i1]/$sum;
        $sum=0;
        for($i1=0;$i1<10;$i1++) $sum+=$deg[$i1];
        if($sum>360.1 or $sum<359.9)$error=1;
        $max=1;
        for($i1=0;$i1<10;$i1++) if($max<$pc[$i1])$max=$pc[$i1];
        if($max<12) $gh=10;
        else $gh=113.0/$max;
        header("Content-type:image/png");
        if($type==0)$this->img=imagecreate(120,120);
        if($type==1)$this->img=imagecreate(160,120);
        $background = imagecolorallocate( $this->img,255, 255, 255);
        imagecolortransparent($this->img,$background);
        $c=array();
        
        // TODO: better overview
        $c[0]=imagecolorallocate($this->img,212,2,29);
        $c[1]=imagecolorallocate($this->img,222,40,80);
        $c[2]=imagecolorallocate($this->img,235,92,150);
        $c[3]=imagecolorallocate($this->img,245,132,203);
        $c[4]=imagecolorallocate($this->img,255,171,255);
        $c[5]=imagecolorallocate($this->img,211,143,224);
        $c[6]=imagecolorallocate($this->img,162,109,167);
        $c[7]=imagecolorallocate($this->img,105,73,148);
        $c[8]=imagecolorallocate($this->img,74,46,134);
        $c[9]=imagecolorallocate($this->img,66,42,112);
        
        $deg2=270;
        for($i1=0;$i1<10 and $error==0 and $type==0;$i1++) {
        	if($deg[$i1]>0)imagefilledarc($this->img,60,60,110,110,$deg2,$deg2+$deg[$i1],$c[$i1],IMG_ARC_PIE);
        	$deg2+=$deg[$i1];
        }
        for($i1=0;$i1<10 and $error==0 and $type==1;$i1++) {
        	imagefilledrectangle($this->img,$i1*16,120-$gh*$pc[$i1]-3,$i1*16+15,117,$c[$i1]);
        }
        for($i1=0;$i1<11 and $error==0 and $type==1 and $gh==10;$i1++) imageline($this->img,0,$i1*10+16,159,$i1*10+16,$background);

        // end copypasting

        parent::display($tpl);

//         imagepng($this->img);
        imagecolordeallocate($this->img,$background);
        for($i1=0;$i1<10;$i1++)imagecolordeallocate($this->img,$c[$i1]);

		// Check for errors.
// 		if (count($errors = $this->get('Errors')))
// 		{
// 			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
//  
// 			return false;
// 		}
		
    }
}
