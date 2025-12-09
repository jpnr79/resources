-- Only rename if upgrading from older versions where table was named 'functions'
-- In fresh installations, the table is already named 'resourcefunctions'
-- RENAME TABLE `glpi_plugin_resources_functions` TO `glpi_plugin_resources_resourcefunctions`;

-- Column already exists in fresh installations
-- ALTER TABLE `glpi_plugin_resources_contracttypes` ADD COLUMN `use_documents_wizard` tinyint NOT NULL DEFAULT '0';
