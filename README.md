#Пример json

```
{
  "name": "Название продукта",
  "sku": "SKU123456", 
  "category_id": 1,
  "product_state_id": 1,
  "product_availability_id": 1,
  "product_location_id": 1,
  "last_system_update": "2025-01-21 10:00:00",
  "manager": {
    "manager": "Иван Иванов",
    "phone": "+7(999)123-45-67"
  },
  "characteristics": {
    "main_characteristic": "Основная характеристика",
    "main_information": "Основная информация", 
    "equipment": "Комплектация",
    "technical_specifications": "Технические характеристики",
    "check_data": "Дата проверки",
    "disassembling_data": "Дата разборки",
    "loading_data": "Дата загрузки",
    "additional_information": "Дополнительная информация"
  },
  "prices": [
    {
      "price": 150000.00,
      "comment": "Цена с НДС",
      "show": true
    }
  ],
  "images": [
    {
      "image": "product1.jpg",
      "main_image": true
    },
    {
      "image": "product2.jpg", 
      "main_image": false
    }
  ]
}
```