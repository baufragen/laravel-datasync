require('json-tree-viewer');

var wrapper = document.getElementById("jsonData");

if (wrapper) {
    var jsonData = wrapper.getAttribute('data-json-data');

    if (jsonData) {
        jsonTree.create(JSON.parse(jsonData), wrapper);
    }
}