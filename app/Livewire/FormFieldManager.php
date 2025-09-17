<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\FormCategory;
use App\Models\FormField;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;
use Illuminate\Validation\Rule;

class FormFieldManager extends Component
{
    public $form;
    public $categories = [];
    public $newCategory = [
        'name' => '',
        'description' => '',
        'percentage_start' => 0,
        'percentage_end' => 100,
    ];
    public $newField = [
        'category_id' => '',
        'label' => '',
        'type' => '',
        'options' => '',
        'required' => false,
        'content' => '',
        'char_limit' => null
    ];

    public $confirmingCategoryDeletion = false;
    public $confirmingFieldDeletion = false;
    public $categoryToDelete;
    public $fieldToDelete;
    public $editingCategory = false;
    public $editingField = false;
    public $categoryBeingEdited;
    public $fieldBeingEdited;

    protected $rules = [
        'newCategory.name' => 'required|string|max:255',
        'newCategory.description' => 'nullable|string',
        'newCategory.percentage_start' => 'required|numeric|min:0|max:100',
        'newCategory.percentage_end' => 'required|numeric|min:0|max:100|gt:newCategory.percentage_start',
        // Keep a generic rule as a fallback; scoped rule is applied at runtime in validation methods
        'newField.category_id' => 'required|exists:form_categories,id',
        'newField.label' => 'required|string|max:255',
        'newField.type' => 'required|in:text,textarea,select,checkbox,radio,file,header,description',
        'newField.options' => 'nullable|string|required_if:newField.type,select,checkbox,radio',
        'newField.required' => 'boolean',
        'newField.content' => 'required_if:newField.type,header,description',
        'categoryBeingEdited.name' => 'required|string|max:255',
        'categoryBeingEdited.description' => 'nullable|string',
        'categoryBeingEdited.percentage_start' => 'required|numeric|min:0|max:100',
        'categoryBeingEdited.percentage_end' => 'required|numeric|min:0|max:100|gt:categoryBeingEdited.percentage_start',
        'fieldBeingEdited.label' => 'required|string|max:255',
        'fieldBeingEdited.type' => 'required|in:text,textarea,select,checkbox,radio,file,header,description',
        'fieldBeingEdited.options' => 'nullable|string|required_if:fieldBeingEdited.type,select,checkbox,radio',
        'fieldBeingEdited.required' => 'boolean',
        'fieldBeingEdited.content' => 'required_if:fieldBeingEdited.type,header,description',

    ];

    public function mount(Form $form): void
    {
        $this->form = $form;
        $this->loadCategories();
    }

    public function loadCategories(): void
    {
        $this->categories = $this->form->categories()->with(['fields' => function ($query) {
            $query->orderBy('order');
        }])->orderBy('order')->get()->toArray();
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function addCategory(): void
    {
        $this->validate([
            'newCategory.name' => 'required|string|max:255',
            'newCategory.description' => 'nullable|string',
            'newCategory.percentage_start' => 'required|numeric|min:0|max:100',
            'newCategory.percentage_end' => 'required|numeric|min:0|max:100|gt:newCategory.percentage_start',
        ]);

        $this->form->categories()->create([
            'name' => $this->newCategory['name'],
            'description' => $this->newCategory['description'],
            'percentage_start' => $this->newCategory['percentage_start'],
            'percentage_end' => $this->newCategory['percentage_end'],
            'order' => $this->form->categories()->max('order') + 1,
        ]);

        $this->reset('newCategory');
        $this->loadCategories();
    }


    public function confirmDeleteCategory($categoryId): void
    {
        $this->confirmingCategoryDeletion = true;
        $this->categoryToDelete = $categoryId;
    }

    public function deleteCategory(): void
    {
        $category = FormCategory::findOrFail($this->categoryToDelete);

        // Delete all fields associated with this category
        $category->fields()->delete();

        // Delete the category
        $category->delete();

        $this->confirmingCategoryDeletion = false;
        $this->categoryToDelete = null;
        $this->loadCategories();
    }

    public function confirmDeleteField($fieldId): void
    {
        $this->confirmingFieldDeletion = true;
        $this->fieldToDelete = $fieldId;
    }

    public function deleteField(): void
    {
        $field = FormField::findOrFail($this->fieldToDelete);
        $field->delete();
        $this->confirmingFieldDeletion = false;
        $this->fieldToDelete = null;
        $this->loadCategories();
    }

    public function addField(): void
    {
        $messages = [
            'newField.category_id.required' => 'Category is required.',
            'newField.category_id.exists' => 'Selected category is invalid.',
        ];

        $this->validate($this->fieldValidationRules(), $messages);

        $category = FormCategory::findOrFail($this->newField['category_id']);

        $fieldData = [
            'form_id' => $this->form->id,
            'form_category_id' => $category->id,
            'type' => $this->newField['type'],
            'order' => $category->fields()->max('order') + 1,
        ];

        if (in_array($this->newField['type'], ['header', 'description'])) {
            $fieldData['content'] = $this->newField['content'];
            $fieldData['label'] = null;
            $fieldData['options'] = null;
            $fieldData['required'] = false;
        } else {
            $fieldData['label'] = $this->newField['label'];
            $fieldData['options'] = $this->newField['options'];
            $fieldData['required'] = $this->newField['required'];
            $fieldData['content'] = null;
        }

        FormField::create($fieldData);

        $this->reset('newField');
        $this->loadCategories();
    }

    public function updatedNewFieldType(): void
    {
        $this->newField['label'] = '';
        $this->newField['options'] = '';
        $this->newField['content'] = '';
        $this->newField['required'] = false;
    }

    private function fieldValidationRules($prefix = 'newField'): array
    {
        $rules = [
            $prefix.'.type' => 'required|in:header,description,text,textarea,select,checkbox,radio,file',
            $prefix.'.category_id' => [
                'required',
                Rule::exists('form_categories', 'id')->where('form_id', $this->form->id),
            ],
        ];

        if (in_array($this->{$prefix}['type'], ['header', 'description'])) {
            $rules[$prefix.'.content'] = 'required|string|max:500';
        } else {
            $rules[$prefix.'.label'] = 'required|string|max:255';
            if (in_array($this->{$prefix}['type'], ['select', 'checkbox', 'radio'])) {
                $rules[$prefix.'.options'] = 'required|string';
            }
            if (in_array($this->{$prefix}['type'], ['text', 'textarea'])) {
                $rules[$prefix.'.char_limit'] = 'nullable|integer|min:1';  // Add this
            }
            $rules[$prefix.'.required'] = 'boolean';
        }

        return $rules;
    }

    public function moveCategoryUp($categoryId): void
    {
        $category = FormCategory::find($categoryId);
        $switchWith = FormCategory::where('form_id', $this->form->id)
            ->where('order', '<', $category->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($switchWith) {
            $tempOrder = $category->order;
            $category->order = $switchWith->order;
            $switchWith->order = $tempOrder;

            $category->save();
            $switchWith->save();

            $this->loadCategories();
        }
    }

    public function moveCategoryDown($categoryId): void
    {
        $category = FormCategory::find($categoryId);
        $switchWith = FormCategory::where('form_id', $this->form->id)
            ->where('order', '>', $category->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($switchWith) {
            $tempOrder = $category->order;
            $category->order = $switchWith->order;
            $switchWith->order = $tempOrder;

            $category->save();
            $switchWith->save();

            $this->loadCategories();
        }
    }

    public function moveFieldUp($fieldId): void
    {
        $field = FormField::find($fieldId);
        $switchWith = FormField::where('form_category_id', $field->form_category_id)
            ->where('order', '<', $field->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($switchWith) {
            $tempOrder = $field->order;
            $field->order = $switchWith->order;
            $switchWith->order = $tempOrder;

            $field->save();
            $switchWith->save();

            $this->loadCategories();
        }
    }

    public function moveFieldDown($fieldId): void
    {
        $field = FormField::find($fieldId);
        $switchWith = FormField::where('form_category_id', $field->form_category_id)
            ->where('order', '>', $field->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($switchWith) {
            $tempOrder = $field->order;
            $field->order = $switchWith->order;
            $switchWith->order = $tempOrder;

            $field->save();
            $switchWith->save();

            $this->loadCategories();
        }
    }
    public function editCategory($categoryId): void
    {
        $this->categoryBeingEdited = FormCategory::find($categoryId)->toArray();
        $this->editingCategory = true;
    }

    public function updateCategory(): void
    {
        $this->validate([
            'categoryBeingEdited.name' => 'required|string|max:255',
            'categoryBeingEdited.description' => 'nullable|string',
            'categoryBeingEdited.percentage_start' => 'required|numeric|min:0|max:100',
            'categoryBeingEdited.percentage_end' => 'required|numeric|min:0|max:100|gt:categoryBeingEdited.percentage_start',
        ]);

        $category = FormCategory::find($this->categoryBeingEdited['id']);
        $category->update($this->categoryBeingEdited);

        $this->editingCategory = false;
        $this->categoryBeingEdited = null;
        $this->loadCategories();
    }

    public function editField($fieldId): void
    {
        $field = FormField::find($fieldId);
        $this->fieldBeingEdited = array_merge($field->toArray(), [
            'required' => (bool) $field->required,
            'options' => $field->options ?? '',
            'content' => $field->content ?? '',
            'char_limit' => $field->char_limit ?? null,
        ]);
        $this->editingField = true;
    }

    public function updateField(): void
    {
        $this->validate($this->fieldValidationRules('fieldBeingEdited'));

        $field = FormField::find($this->fieldBeingEdited['id']);

        // Ensure boolean value is properly set
        $this->fieldBeingEdited['required'] = (bool) $this->fieldBeingEdited['required'];

        // Handle null values appropriately
        if (in_array($this->fieldBeingEdited['type'], ['header', 'description'])) {
            $this->fieldBeingEdited['label'] = null;
            $this->fieldBeingEdited['options'] = null;
            $this->fieldBeingEdited['required'] = false;
        } else {
            $this->fieldBeingEdited['content'] = null;
            if (!in_array($this->fieldBeingEdited['type'], ['select', 'checkbox', 'radio'])) {
                $this->fieldBeingEdited['options'] = null;
            }
        }

        $field->update($this->fieldBeingEdited);

        $this->editingField = false;
        $this->fieldBeingEdited = null;
        $this->loadCategories();
    }



    public function render(): View|Factory|Application
    {
        return view('livewire.form-field-manager');
    }
}
