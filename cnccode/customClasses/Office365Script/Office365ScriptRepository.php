<?php


namespace CNCLTD\Office365Script;


interface Office365ScriptRepository
{
    public function getById(Office365ScriptId $id);
    public function add(Office365Script $office365Script);
    public function delete(Office365Script $office365Script);
    public function update(Office365Script $office365Script);
}