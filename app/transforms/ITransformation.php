<?php

namespace Transform;

interface ITransformation
{
    public function setProjectName($projectName);

    public function setBuildPath($path);
    public function setConfig(array $config);

    public function make($includeProjectBase);
}