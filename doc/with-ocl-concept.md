```plantuml
@startuml
skinparam componentStyle rectangle
skinparam linetype ortho

frame "PCMT" {
    component levo [
        Levonorgestrel/Ethinyl Estradiol 150/30 mcg + Placebo 28 Tablets/Cycle

        UOM: Cycle
    ]

    component levo_mylan [
        Mylan Levonorgestrel/Ethinyl Estradiol 150/30 mcg + Placebo 28 Tablets/Cycle

        Brand:  Zinnia-P
        Market Auth:  12345
    ]

    [levo] ----> [levo_mylan]: manufacturer authorized in country
}

frame "OCL" {
    component term_levo [
                Levonorgestrel/Ethinyl Estradiol 150/30 mcg + Placebo 28 Tablets/Cycle
    ]

    [levo] <---> [term_levo]: terminology to inventory item
}

frame "ICD-11" {
    component icd11_levo [
        XM6U53 Levonorgestrel
    ]

    [levo] ---> [icd11_levo]: ICD-11 coding
    [term_levo] --> [icd11_levo]: ICD-11 mapping
}

