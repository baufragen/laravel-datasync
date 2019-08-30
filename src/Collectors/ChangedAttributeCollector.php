<?php

namespace Baufragen\DataSync\Collectors;

class ChangedAttributeCollector extends AttributeCollector {
    protected function getSyncedAttributes() {
        return collect($this->model->getDirtySyncedAttributeData());
    }
}
