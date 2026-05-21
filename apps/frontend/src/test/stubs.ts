import { defineComponent, h } from 'vue';

export const LayoutStub = defineComponent({
  name: 'LayoutStub',
  setup(_, { slots }) {
    return () => h('div', { 'data-testid': 'layout-stub' }, slots.default?.());
  },
});

export const CardStub = defineComponent({
  name: 'CardStub',
  setup(_, { slots }) {
    return () =>
      h('div', { 'data-testid': 'card-stub' }, [
        slots.header?.(),
        slots.content?.(),
        slots.default?.(),
        slots.footer?.(),
      ]);
  },
});

export const ButtonStub = defineComponent({
  name: 'ButtonStub',
  props: {
    label: { type: String, default: '' },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
  },
  emits: ['click'],
  setup(props, { slots, emit, attrs }) {
    return () =>
      h(
        'button',
        {
          ...attrs,
          type: props.type,
          disabled: props.disabled || props.loading,
          onClick: (event: MouseEvent) => emit('click', event),
        },
        slots.default?.() ?? props.label,
      );
  },
});

export const InputTextStub = defineComponent({
  name: 'InputTextStub',
  inheritAttrs: false,
  props: {
    modelValue: { type: [String, Number], default: '' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    autocomplete: { type: String, default: '' },
  },
  emits: ['update:modelValue'],
  setup(props, { attrs, emit }) {
    return () =>
      h('input', {
        ...attrs,
        type: props.type,
        value: props.modelValue,
        placeholder: props.placeholder,
        autocomplete: props.autocomplete,
        onInput: (event: Event) =>
          emit('update:modelValue', (event.target as HTMLInputElement).value),
      });
  },
});

export const TextareaStub = defineComponent({
  name: 'TextareaStub',
  inheritAttrs: false,
  props: {
    modelValue: { type: String, default: '' },
  },
  emits: ['update:modelValue'],
  setup(props, { attrs, emit }) {
    return () =>
      h('textarea', {
        ...attrs,
        value: props.modelValue,
        onInput: (event: Event) =>
          emit('update:modelValue', (event.target as HTMLTextAreaElement).value),
      });
  },
});

export const SelectStub = defineComponent({
  name: 'SelectStub',
  inheritAttrs: false,
  props: {
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, default: () => [] },
    optionLabel: { type: String, default: 'label' },
    optionValue: { type: String, default: 'value' },
    placeholder: { type: String, default: '' },
    showClear: { type: Boolean, default: false },
  },
  emits: ['update:modelValue'],
  setup(props, { attrs, emit }) {
    return () =>
      h(
        'select',
        {
          ...attrs,
          value: props.modelValue,
          onChange: (event: Event) =>
            emit('update:modelValue', (event.target as HTMLSelectElement).value),
        },
        [
          props.showClear || props.placeholder
            ? h('option', { value: '' }, props.placeholder || 'Select')
            : null,
          ...(props.options as Array<Record<string, string | number>>).map((option) =>
            h(
              'option',
              { value: option[props.optionValue] as string | number },
              String(option[props.optionLabel]),
            ),
          ),
        ],
      );
  },
});

export const CheckboxStub = defineComponent({
  name: 'CheckboxStub',
  props: {
    modelValue: { type: Boolean, default: false },
    binary: { type: Boolean, default: true },
  },
  emits: ['update:modelValue'],
  setup(props, { emit, attrs }) {
    return () =>
      h('input', {
        ...attrs,
        type: 'checkbox',
        checked: props.modelValue,
        onChange: (event: Event) =>
          emit('update:modelValue', (event.target as HTMLInputElement).checked),
      });
  },
});

export const TagStub = defineComponent({
  name: 'TagStub',
  props: {
    value: { type: String, default: '' },
  },
  setup(props, { slots }) {
    return () => h('span', { 'data-testid': 'tag-stub' }, slots.default?.() ?? props.value);
  },
});

export const ProgressSpinnerStub = defineComponent({
  name: 'ProgressSpinnerStub',
  setup() {
    return () => h('div', { 'data-testid': 'spinner-stub' }, 'Loading');
  },
});

export const SkeletonStub = defineComponent({
  name: 'SkeletonStub',
  setup() {
    return () => h('div', { 'data-testid': 'skeleton-stub' });
  },
});

export const DialogStub = defineComponent({
  name: 'DialogStub',
  props: {
    visible: { type: Boolean, default: false },
  },
  setup(props, { slots }) {
    return () => (props.visible ? h('div', { 'data-testid': 'dialog-stub' }, [slots.default?.(), slots.footer?.()]) : null);
  },
});

export const DataTableStub = defineComponent({
  name: 'DataTableStub',
  setup(_, { slots }) {
    return () => h('div', { 'data-testid': 'datatable-stub' }, slots.default?.());
  },
});

export const ColumnStub = defineComponent({
  name: 'ColumnStub',
  setup() {
    return () => null;
  },
});

export const TourCardStub = defineComponent({
  name: 'TourCardStub',
  props: {
    tour: { type: Object, required: false, default: null },
  },
  setup(props) {
    return () =>
      h('div', { 'data-testid': 'tour-card-stub' }, props.tour ? String((props.tour as { title?: string }).title ?? '') : '');
  },
});

export const baseGlobalStubs = {
  Card: CardStub,
  Button: ButtonStub,
  InputText: InputTextStub,
  Textarea: TextareaStub,
  Select: SelectStub,
  Checkbox: CheckboxStub,
  Tag: TagStub,
  ProgressSpinner: ProgressSpinnerStub,
  Skeleton: SkeletonStub,
  Dialog: DialogStub,
  DataTable: DataTableStub,
  Column: ColumnStub,
  PublicLayout: LayoutStub,
};
