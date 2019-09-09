import jsonTree from 'json-tree-viewer'

var wrapper = document.getElementById("jsonData");

if (wrapper) {
    var jsonData = wrapper.getAttribute('data-json-data');

    if (jsonData) {
        var tree = jsonTree.create(JSON.parse(jsonData), wrapper);

        tree.expand((node) => {
            return ![
                'connection',
                'apikey',
                'encrypted',
                'model',
                'identifier',
            ].includes(node.name);
        });
    }
}