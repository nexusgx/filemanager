<?php if(!isset($fm)) die(); //die if there is no file manager class. maybe being accessed directly ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>File Manager</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="css/filemanager.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <div id="prompt">
        <div class="text">Ask a question here</div>
        <div class="input"><input type="text"></div>
        <div class="btn-group pull-right" role="group">
            <button type="button" class="btn btn-default" data-func="cancel">
                Cancel
            </button>
            <button type="button" class="btn btn-default" data-func="ok">
                OK
            </button>
        </div>
    </div>
    <div id="filemanager">
        <div id="toolbar">
            <div id="tools">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default" id="upload">
                        <span class="glyphicon glyphicon-upload"></span>
                    </button>
                    <button type="button" class="btn btn-default" id="new_file">
                        <span class="glyphicon glyphicon-file"></span>
                    </button>
                    <button type="button" class="btn btn-default" id="new_dir">
                        <span class="glyphicon glyphicon-folder-close"></span>
                    </button>
                </div>
            </div>
            <div id="view">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default" data-view="icons">
                        <span class="glyphicon glyphicon-th-large"></span>
                    </button>
                    <button type="button" class="btn btn-default" data-view="list">
                        <span class="glyphicon glyphicon-align-justify"></span>
                    </button>
                    <button type="button" class="btn btn-default" data-view="cols">
                        <span class="glyphicon glyphicon-th-list"></span>
                    </button>
                </div>
            </div>
            <div id="edit" style="display:none;">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default single" data-action="rename">
                        <span class="glyphicon glyphicon-pencil"></span>
                    </button>
                    <button type="button" class="btn btn-default" data-action="delete">
                        <span class="glyphicon glyphicon-trash"></span>
                    </button>
                </div>
            </div>
        </div>
        <nav>
            <ol class="breadcrumb">
                <?php if($nav)foreach($nav as $n){
                ?>
                <li><a data-path="<?php echo $n['rel_path']; ?>"><?php echo $n['name'];?></a></li>
                <?php } ?>
            </ol>
            <div id="search">
                <div class="input-group">
                    <input type="text" class="form-control" aria-label="..." placeholder="Search in directory">
                    <span id="searchclear" class="glyphicon glyphicon-remove-circle"></span>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        <div id="files" class="icons">
            <?php if($files)foreach($files as $i=>$f){
            ?>
            <div data-file="<?php echo $f['rel_filename'].'" class="file '.$f['type'];?>" title="<?php echo $f['filename'];?>" data-i="<?php echo $i;?>">
                
                <div class="icon"><i class="fa fa-<?php echo $f['icon'];?>"></i></div>
                <div class="name"><?php echo $f['filename'];?></div>
                <div class="type"><?php echo $f['type'];?></div>
                <div class="filedate"><?php echo date('m/d/Y g:i:s',$f['modified_timestamp']);?></div>
                <div class="filesize" data-size="<?php echo $f['raw_size'];?>"><?php echo $f['filesize'];?></div>
            </div>
            <?php } ?>
        </div>
        <footer>
            <div id="info"></div>
            <div id="copyright">&copy;2016 Tiny Tinker File Manager</div>
        </footer>
    </div>
    <div id="iframe" class="hidden"><iframe id="iframe_up" name="iframe_up"></iframe></div>
    <div class="lightbox">
        <div class="lightbox-close"><i class="glyphicon glyphicon-remove-circle"></i></div>
        <div class="lightbox-header"></div>
        <div class="lightbox-content">
            <div id="text"><textarea></textarea></div>
            <div id="image"></div>
        </div>
    </div>
    <div id="fade" class="black_overlay"></div>
    <div id="file_upload" class="hidden">
        <form method="post" enctype="multipart/form-data" target="iframe_up">
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="dir">
            <input type="file" name="upload[]" multiple="multiple">
        </form>
    </div>
    <script>var base_url='<?php echo $config['base_url']; ?>';</script>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    <script src="js/filemanager.min.js"></script>
</body>

</html>
