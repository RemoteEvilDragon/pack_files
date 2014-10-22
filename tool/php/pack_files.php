<?php

require_once(__DIR__ . '/FilesPacker.php');

$options = array(
    array('h',   'help',       0,      false,       'show help'),
    array('i',   'src',        1,      null,        'source files directory'),
    array('o',   'output',     1,      null,        'output directory'),
    array('ox',  'ext',        1,      null,        'output files extension'),
    array('p',   'prefix',     1,      '',          'package prefix name'),
    array('ek',  'key',        1,      null,        'encrypt key'),
);

function errorhelp()
{
    print("\nshow help:\n    pack_files -h\n\n");
}

function help()
{
    global $options;

    echo <<<EOT

usage: pack_files -i src -o output ...

options:

EOT;

    for ($i = 0; $i < count($options); $i++)
    {
        $o = $options[$i];
        printf("    -%s %s\n", $o[0], $o[4]);
    }

    echo <<<EOT

config file format:

    return array(
        'src'      => source files directory,
        'output'   => output directory,
        'ext'      => output files extension,
        'prefix'   => package prefix name,
        'key'      => encrypt key,
    );

examples:

    # encrypt res/*.* to renews/, with specifies key (contain 16 chars max)
    pack_files -i res -o resnew -ek XXTEA

    # encrypt res/*.* to renews/, with specifies key, filename extension
    pack_files -i res -o resnew -ek XXTEA -ox dat(or .dat)

    ./pack_files.sh -i /Users/lansey/Desktop/test -o /Users/lansey/Desktop/1111 -ek lanseys1231 -ox dat
    pack_files.bat -i C:/test/ -o C:/1111 -ek lanseys1231 -ox dat


EOT;

}

// ----

print("\n");
if ($argc < 2)
{
    help();
    return(1);
}

$config = fetchCommandLineArguments($argv, $options, 4);
if (!$config)
{
    errorhelp();
    return(1);
}

if ($config['help'])
{
    help();
    return(0);
}

$packer = new FilesPacker($config, $options);
if ($packer->validateConfig())
{
    return($packer->run());
}
else
{
    errorhelp();
    return(1);
}
