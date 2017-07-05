<?php
$d = dir( '\\\\2sime2\\2sdata5lun0\\BennettGriffin\\FERRING3FS' );
while ( $e = $d->read() ){
  if ( preg_match( '/.*-cd.spi$/i' , $e ) ){
    $dir[] = $e;
  }
}
rsort( $dir, SORT_NATURAL );
var_dump( $dir );
?>