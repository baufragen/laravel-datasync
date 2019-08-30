<?php

namespace Baufragen\DataSync\Collectors;

class AllAttributeCollector extends AttributeCollector {
    protected function getSyncedAttributes() {
        return collect($this->model->getAllSyncedAttributeData());
    }
}
