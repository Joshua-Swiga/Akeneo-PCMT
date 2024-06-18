#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

echo "STARTING SELENIUM TESTS..."

./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors --suite=selenium-core
./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors --suite=selenium-drafts
./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors --suite=selenium-permissions
./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors --suite=selenium-rules

# to run specific test, comment above lines and uncomment below:
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtDraftBundle/FunctionalTests/features/selenium/approve_draft.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/create_concatenated_attribute.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/create_reference_data_county_code.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/create_reference_data_language_code.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtDraftBundle/FunctionalTests/features/selenium/approve_draft.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtDraftBundle/FunctionalTests/features/selenium/bulk_approve.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/create_family.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtCoreBundle/FunctionalTests/features/selenium/edit_family.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/11_create_select_options_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/13_edit_select_options_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/15_run_select_options_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/18_delete_select_options_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/21_create_family_to_family_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/23_edit_family_to_family_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/25_run_family_to_family_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/28_delete_family_to_family_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/31_create_pull_images_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/33_edit_pull_images_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/35_run_pull_images_rule.feature
#./ddev.sh exec -T fpm /srv/pim/vendor/bin/behat --profile $PROFILE --colors src/PcmtRulesBundle/FunctionalTests/features/selenium/38_delete_pull_images_rule.feature
