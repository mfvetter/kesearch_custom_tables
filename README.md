# kesearch_custom_tables
KeSearch Indexer for Custom Table Elements

This extension was based on the Flux Kesearch Indexer extension:

https://extensions.typo3.org/extension/flux_kesearch_indexer
https://github.com/MamounAlsmaiel/flux_kesearch_indexer

This extension extends the standard ke_search content record indexers so that custom tables and fields can be indexed.

Currently only suports Typo3 11.5.

## Configuration
Add the following configuration to your typoscript.


```
plugin.tx_kesearch_custom_tables {
  config {
    elements {
      accordion {
        table = tx_bootstrappackage_accordion_item
        fields = header,bodytext
      }
      tab {
        table = tx_bootstrappackage_accordion_item
        fields = header,bodytext
      }
      carousel {
        table = tx_bootstrappackage_carousel_item
        fields = header,subheader,bodytext
      }
      icon_group {
        table = tx_bootstrappackage_icon_group_item
        fields = header,subheader
      }
      card_group {
        table = tx_bootstrappackage_card_group_item
        fields = header,bodytext
      }
      timeline {
        table = tx_bootstrappackage_timeline_item
        fields = header,bodytext
      }
    }
  }
}
```

## Limitations
At this moment, this extension doesn't index linked files from the content of the custom tables.    

It also doesn't work with the scheduler as the configuration is based on TypoScript only.