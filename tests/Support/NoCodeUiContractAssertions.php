<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NoCodeUiContractAssertions
{
    /**
     * @return array<string,mixed>
     */
    public static function readJsonFile(TestCase $test, string $path): array
    {
        TestCase::assertFileExists($path);
        $decoded = json_decode((string) file_get_contents($path), true);
        TestCase::assertIsArray($decoded, 'JSON file should decode to an object: ' . $path);

        return $decoded;
    }

    /**
     * @param array<string,mixed> $runtimePreview
     * @param list<string> $expectedScreenKeys
     */
    public static function assertRuntimePreviewScreenKeys(
        TestCase $test,
        array $runtimePreview,
        array $expectedScreenKeys,
    ): void {
        $screens = is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [];
        TestCase::assertSame(
            $expectedScreenKeys,
            array_values(array_map(static fn (array $screen): string => (string) ($screen['screen_key'] ?? ''), $screens)),
        );
    }

    /**
     * @param array<string,mixed> $runtimePreview
     * @param array<string,mixed> $expectedField
     */
    public static function assertRuntimePreviewScreenField(
        TestCase $test,
        array $runtimePreview,
        string $screenKey,
        array $expectedField,
    ): void {
        $screens = is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [];
        $matchedScreen = [];
        foreach ($screens as $screen) {
            if (is_array($screen) && (string) ($screen['screen_key'] ?? '') === $screenKey) {
                $matchedScreen = $screen;
                break;
            }
        }
        TestCase::assertNotSame([], $matchedScreen, 'runtime preview screen exists: ' . $screenKey);

        $fieldKey = (string) ($expectedField['field_key'] ?? '');
        $fields = is_array($matchedScreen['fields'] ?? null) ? $matchedScreen['fields'] : [];
        $matchedField = [];
        foreach ($fields as $field) {
            if (is_array($field) && (string) ($field['field_key'] ?? '') === $fieldKey) {
                $matchedField = $field;
                break;
            }
        }
        TestCase::assertNotSame([], $matchedField, 'runtime preview field exists: ' . $fieldKey);

        foreach ($expectedField as $key => $expectedValue) {
            TestCase::assertSame($expectedValue, $matchedField[$key] ?? null, 'runtime preview field ' . $fieldKey . ' ' . $key);
        }
    }

    /**
     * @param array<string,string> $screenTypesByKey
     */
    public static function assertPreviewHtmlScreens(
        TestCase $test,
        string $html,
        array $screenTypesByKey,
    ): void {
        $xpath = self::htmlXPath($html);

        foreach ($screenTypesByKey as $screenKey => $screenType) {
            self::assertXPathCount(
                $xpath,
                '//*[@data-screen-key=' . self::xpathLiteral($screenKey) . ' and @data-screen-type=' . self::xpathLiteral($screenType) . ']',
                1,
                'screen marker exists: ' . $screenKey,
            );
            self::assertXPathCount(
                $xpath,
                '//*[@data-screen-body=' . self::xpathLiteral($screenKey) . ']',
                1,
                'screen body marker exists: ' . $screenKey,
            );
            self::assertXPathCount(
                $xpath,
                '//*[@data-screen-summary=' . self::xpathLiteral($screenKey) . ']',
                1,
                'screen summary marker exists: ' . $screenKey,
            );
        }
    }

    /**
     * @param list<string> $fieldNames
     */
    public static function assertPreviewHtmlFormFields(TestCase $test, string $html, array $fieldNames): void
    {
        $xpath = self::htmlXPath($html);

        foreach ($fieldNames as $fieldName) {
            self::assertXPathCount(
                $xpath,
                '//*[@name=' . self::xpathLiteral($fieldName) . ']',
                1,
                'form field exists: ' . $fieldName,
            );
        }
    }

    /**
     * @param list<string> $actionKeys
     */
    public static function assertPreviewHtmlDisabledExtensionActions(
        TestCase $test,
        string $html,
        array $actionKeys,
    ): void {
        $xpath = self::htmlXPath($html);

        foreach ($actionKeys as $actionKey) {
            self::assertXPathCount(
                $xpath,
                '//button[@data-extension-slot-action=' . self::xpathLiteral($actionKey) . ' and @disabled and @data-generated-button-enabled="false"]',
                1,
                'disabled extension action button exists: ' . $actionKey,
            );
            self::assertXPathCount(
                $xpath,
                '//*[@data-extension-slot-route-boundary=' . self::xpathLiteral($actionKey) . ']',
                1,
                'extension action route boundary marker exists: ' . $actionKey,
            );
        }
    }

    /**
     * @param list<array<string,mixed>> $actions
     */
    public static function assertPreviewHtmlDisabledManagedActions(
        TestCase $test,
        string $html,
        array $actions,
    ): void {
        $xpath = self::htmlXPath($html);

        foreach ($actions as $action) {
            $actionKey = (string) ($action['action_key'] ?? '');
            $disabledReason = (string) ($action['disabled_reason'] ?? '');
            $affordance = (string) ($action['affordance'] ?? '');
            $keyboardActivation = (string) ($action['keyboard_activation'] ?? '');
            $expectedOccurrences = (int) ($action['expected_dom_occurrences'] ?? 1);

            self::assertXPathCount(
                $xpath,
                '//button[@data-action-key=' . self::xpathLiteral($actionKey)
                    . ' and @disabled'
                    . ' and @data-action-enabled="false"'
                    . ' and @data-action-state="disabled"'
                    . ' and @aria-disabled="true"'
                    . ' and @data-action-disabled-reason=' . self::xpathLiteral($disabledReason)
                    . ' and @data-action-affordance=' . self::xpathLiteral($affordance)
                    . ' and @data-keyboard-activation=' . self::xpathLiteral($keyboardActivation)
                    . ']',
                $expectedOccurrences,
                'disabled managed action button exists: ' . $actionKey,
            );
            self::assertXPathCount(
                $xpath,
                '//*[@data-action-hint=' . self::xpathLiteral($actionKey) . ' and @data-action-state-hint="disabled"]',
                $expectedOccurrences,
                'disabled managed action hint exists: ' . $actionKey,
            );
        }
    }

    private static function htmlXPath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        TestCase::assertTrue($loaded, 'Generated runtime preview HTML should parse with DOMDocument.');

        return new DOMXPath($document);
    }

    private static function assertXPathCount(DOMXPath $xpath, string $query, int $expectedCount, string $message): void
    {
        $nodes = $xpath->query($query);
        TestCase::assertNotFalse($nodes, 'XPath query should be valid: ' . $query);
        TestCase::assertSame($expectedCount, $nodes->length, $message);
    }

    private static function xpathLiteral(string $value): string
    {
        if (!str_contains($value, "'")) {
            return "'" . $value . "'";
        }
        if (!str_contains($value, '"')) {
            return '"' . $value . '"';
        }

        $parts = array_map(
            static fn (string $part): string => "'" . $part . "'",
            explode("'", $value),
        );

        return 'concat(' . implode(', "\'", ', $parts) . ')';
    }
}
