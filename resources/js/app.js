import jsonTree from 'json-tree-viewer'

var wrapper = document.getElementById("jsonData");

if (wrapper) {
    var jsonData = wrapper.getAttribute('data-json-data');

    if (jsonData) {
        var tree = jsonTree.create(JSON.parse(jsonData), wrapper);

        tree.expand((node) => {
            var expand = true;
            node.childNodes.forEach((childNode) => {
                if (childNode.label === 'name') {
                    var nodeValue = childNode.el.lastChild.lastChild.innerText;

                    if (nodeValue) {
                        if (['connection', 'apikey', 'encrypted', 'model', 'identifier'].includes(nodeValue.replace(/"/g, ''))) {
                            expand = false;
                        }
                    }
                }
            });
            return expand;
        });
    }
}