<?php

namespace App\Form;

use App\DTO\Payment\CreatePaymentDTO;
use App\Entity\TeamUser;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamUserId', EntityType::class, [
                'label' => 'Team Member',
                'class' => TeamUser::class,
                'choice_label' => function (TeamUser $teamUser) {
                    return $teamUser->getUser()->getName() . ' (' . $teamUser->getTeam()->getName() . ')';
                },
                'choice_value' => function (?TeamUser $teamUser) {
                    return $teamUser ? $teamUser->getId()->toString() : '';
                },
                'placeholder' => 'Select a team member',
                'required' => true,
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                'currency' => false,
                'divisor' => 100,
                'html5' => true,
                'required' => true,
            ])
            ->add('currency', ChoiceType::class, [
                'label' => 'Currency',
                'choices' => array_combine(
                    array_map(fn(CurrencyEnum $currency) => $currency->getSymbol() . ' ' . $currency->value, CurrencyEnum::cases()),
                    array_map(fn(CurrencyEnum $currency) => $currency->value, CurrencyEnum::cases())
                ),
                'placeholder' => 'Select currency',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Payment Type',
                'choices' => array_combine(
                    array_map(fn(PaymentTypeEnum $type) => $type->getLabel(), PaymentTypeEnum::cases()),
                    array_map(fn(PaymentTypeEnum $type) => $type->value, PaymentTypeEnum::cases())
                ),
                'placeholder' => 'Select payment type',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('reference', TextType::class, [
                'label' => 'Reference',
                'required' => false,
                'help' => 'Required for bank transfers, credit cards, and mobile payments',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreatePaymentDTO::class,
            'csrf_protection' => true,
        ]);
    }
}
