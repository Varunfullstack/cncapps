<?php


$files = scandir(__DIR__ . '/..');

$phpFiles = [];
foreach ($files as $file) {

    if (preg_match(
        '/^.*\.php$/',
        $file
    )) {
        $phpFiles[] = $file;
    }
}

?>
<style>
    .success {
        color: green;
    }

    .fail {
        color: red;
    }
</style>

<div id="result">

</div>

<script>
    var phpFiles = <?=  json_encode($phpFiles) ?>;
    var resultElement = document.getElementById('result');

    phpFiles.map(
        phpFile => {

            var url = window.location.protocol + "//" + window.location.host + '/' + phpFile;

            return fetch(url)
                .catch((error) => {
                    console.log(error);
                    debugger;
                })
                .then(
                    (response) => {
                        if (!response.ok) {
                            return false;
                        }
                        return response.text().then(text => {
                            return text.indexOf('Fatal error') < 0 && text.indexOf('Warning') < 0
                        })
                    }
                ).then(success => {

                    var element = document.createElement('div');
                    if (success) {

                        element.className = 'success';
                        element.innerText = phpFile + " was tested successfully";
                    } else {
                        element.className = 'fail';
                        element.append(`${phpFile} failed miserably `);
                        var link = document.createElement('a');

                        link.href = url;
                        link.setAttribute('target', '_blank');
                        link.innerText = url;
                        element.append(link)
                    }
                    resultElement.append(
                        element
                    )
                })
        }
    )


</script>
