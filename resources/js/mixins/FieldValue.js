import filled from '../util/filled'
import isArray from 'lodash/isArray'

export default {
  props: ['field'],

  methods: {
    /**
     * @param {any} value
     * @returns {boolean}
     */
    isEqualsToValue(value) {
      if (isArray(this.field.value) && filled(value)) {
        return Boolean(
          this.field.value.includes(value) ||
            this.field.value.includes(value.toString())
        )
      }

      return Boolean(
        this.field.value === value ||
          this.field.value?.toString() === value ||
          this.field.value === value?.toString() ||
          this.field.value?.toString() === value?.toString()
      )
    },
  },

  computed: {
    /**
     * @returns {string}
     */
    fieldAttribute() {
      return this.field.attribute
    },

    /**
     * @returns {boolean}
     */
    fieldHasValue() {
      return filled(this.field.value)
    },

    /**
     * @returns {boolean}
     */
    usesCustomizedDisplay() {
      return this.field.usesCustomizedDisplay && filled(this.field.displayedAs)
    },

    /**
     * @returns {boolean}
     */
    fieldHasValueOrCustomizedDisplay() {
      return this.usesCustomizedDisplay || this.fieldHasValue
    },

    /**
     * @returns {string|null}
     */
    fieldValue() {
      if (!this.fieldHasValueOrCustomizedDisplay) {
        return null
      }

      return String(this.field.displayedAs ?? this.field.value)
    },

    /**
     * @returns {string|null}
     */
    shouldDisplayAsHtml() {
      return this.field.asHtml
    },
  },
}
